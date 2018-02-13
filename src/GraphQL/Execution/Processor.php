<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\graphql\GraphQL\Execution\Visitor\VisitorInterface;
use Youshido\GraphQL\Exception\ResolveException;
use Youshido\GraphQL\Execution\DeferredResolverInterface;
use Youshido\GraphQL\Execution\DeferredResult;
use Youshido\GraphQL\Execution\Processor as BaseProcessor;
use Youshido\GraphQL\Execution\Request;
use Youshido\GraphQL\Field\FieldInterface;
use Youshido\GraphQL\Parser\Parser;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Type\Enum\AbstractEnumType;
use Youshido\GraphQL\Type\Scalar\AbstractScalarType;
use Youshido\GraphQL\Validator\RequestValidator\RequestValidator;

class Processor extends BaseProcessor {

  /**
   * @var \Drupal\graphql\GraphQL\Execution\Reducer
   */
  protected $requestReducer;

  /**
   * Processor constructor.
   *
   * @param \Youshido\GraphQL\Schema\AbstractSchema $schema
   * @param $query
   * @param array $variables
   */
  public function __construct(AbstractSchema $schema, $query, array $variables = []) {
    parent::__construct($schema);

    $parser = new Parser();
    $request = new Request($parser->parse($query), $variables);

    // Validation of the query payload. This does not take into consideration
    // any schema specifics and simply validations the structure of the query.
    $validator = new RequestValidator();
    $validator->validate($request);

    // Set the request in the execution context.
    $this->executionContext->setRequest($request);

    // Create a request reducer for our query visitors.
    $this->requestReducer = new Reducer($this->executionContext);
  }

  /**
   * @param \Drupal\graphql\GraphQL\Execution\Visitor\VisitorInterface $visitor
   * @param callable $callback
   *
   * @return null
   */
  public function reduceRequest(VisitorInterface $visitor, callable $callback) {
    try {
      $result = $this->requestReducer->reduceRequest($visitor);

      // Call the 'finish' callback. This is useful because that way the
      // exceptions are caught here and can be written into the execution
      // context.
      return $callback($result);
    }
    catch (ResolveException $exception) {
      $this->executionContext->addError($exception);
    }

    return NULL;
  }

  /**
   * @return array|mixed
   */
  public function resolveRequest() {
    $output = [];

    if (!$this->executionContext->hasErrors()) {
      try {
        $operations = $this->executionContext->getRequest()->getAllOperations();

        foreach ($operations as $query) {
          if ($result = $this->resolveQuery($query)) {
            $output = array_replace_recursive($output, $result);
          };
        }

        // If the processor found any deferred results, resolve them now.
        if (!empty($output) && (!empty($this->deferredResultsLeaf) || !empty($this->deferredResultsComplex))) {
          try {
            while ($resolver = array_shift($this->deferredResultsComplex)) {
              $resolver->resolve();
            }

            // Deferred scalar and enum fields should be resolved last to pick up
            // as many as possible for a single batch.
            while ($resolver = array_shift($this->deferredResultsLeaf)) {
              $resolver->resolve();
            }
          }
          catch (ResolveException $exception) {
            $this->executionContext->addError($exception);
          }
          finally {
            $output = static::unpackDeferredResults($output);
          }
        }
      }
      catch (ResolveException $exception) {
        $this->executionContext->addError($exception);
      }
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  protected function deferredResolve($resolvedValue, FieldInterface $field, callable $callback) {
    if ($resolvedValue instanceof DeferredResolverInterface) {
      $deferredResult = new DeferredResult($resolvedValue, function ($resolvedValue) use ($field, $callback) {
        // Allow nested deferred resolvers.
        return $this->deferredResolve($resolvedValue, $field, $callback);
      });

      // Whenever we stumble upon a deferred resolver, add it to the queue to be
      // resolved later.
      $type = $field->getType()->getNamedType();
      if ($type instanceof AbstractScalarType || $type instanceof AbstractEnumType) {
        array_push($this->deferredResultsLeaf, $deferredResult);
      }
      else {
        array_push($this->deferredResultsComplex, $deferredResult);
      }

      return $deferredResult;
    }

    // For simple values, invoke the callback immediately.
    return $callback($resolvedValue);
  }

}