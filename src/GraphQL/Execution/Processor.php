<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\SecureFieldInterface;
use Drupal\graphql\GraphQL\ValueWrapperInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\DeferredResolverInterface;
use Youshido\GraphQL\Execution\DeferredResult;
use Youshido\GraphQL\Execution\Processor as BaseProcessor;
use Youshido\GraphQL\Field\FieldInterface;
use Youshido\GraphQL\Parser\Ast\Interfaces\FieldInterface as AstFieldInterface;
use Youshido\GraphQL\Parser\Ast\Query as AstQuery;
use Youshido\GraphQL\Schema\AbstractSchema;

class Processor extends BaseProcessor {

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Constructs a Processor object.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The dependency injection container.
   * @param \Youshido\GraphQL\Schema\AbstractSchema $schema
   *   The GraphQL schema.
   * @param boolean $secure
   *   Indicate that this processor is executing trusted queries.
   */
  public function __construct(ContainerInterface $container, AbstractSchema $schema, $secure = FALSE) {
    parent::__construct($schema);
    $this->container = $container;

    $contexts = $this->executionContext->getContainer();
    $contexts->set('secure', $secure);
    $contexts->set('metadata', new CacheableMetadata());
  }

  /**
   * {@inheritdoc}
   */
  protected function doResolve(FieldInterface $field, AstFieldInterface $ast, $parentValue = NULL) {
    $contexts = $this->executionContext->getContainer();

    if ($field instanceof SecureFieldInterface) {
      // If not resolving in a trusted environment, check if the field is secure.
      if ($contexts->has('secure') && !$contexts->get('secure') && !$field->isSecure()) {
        throw new \Exception(sprintf("Unable to resolve insecure field '%s' (%s).", $field->getName(), get_class($field)));
      }
    }

    $value = $this->doResolveValue($field, $ast, $parentValue);
    if ($value instanceof CacheableDependencyInterface && $contexts->has('metadata')) {
      // If the current resolved value returns cache metadata, keep it.
      $contexts->get('metadata')->addCacheableDependency($value);
    }

    // If it's a value wrapper, extract the real value to return.
    if ($value instanceof ValueWrapperInterface) {
      $value = $value->getValue();
    }

    return $value;
  }

  /**
   * Override deferred resolving to use our own DeferredResult class.
   *
   * {@inheritdoc}
   */
  protected function deferredResolve($resolvedValue, callable $callback) {
    if ($resolvedValue instanceof DeferredResolverInterface) {
      $deferredResult = new DeferredResult($resolvedValue, function($result) use ($callback) {
        $contexts = $this->executionContext->getContainer();

        if ($result instanceof CacheableDependencyInterface && $contexts->has('metadata')) {
          $contexts->get('metadata')->addCacheableDependency($result);
        }

        if ($result instanceof ValueWrapperInterface) {
          $result = $result->getValue();
        }

        return $this->deferredResolve($result, $callback);
      });

      // Whenever we stumble upon a deferred resolver, append it to the
      // queue to be resolved later.
      $this->deferredResults[] = $deferredResult;
      return $deferredResult;
    }

    // For simple values, invoke the callback immediately.
    return $callback($resolvedValue);
  }

  /**
   * Helper function to resolve a field value.
   *
   * @param \Youshido\GraphQL\Field\FieldInterface $field
   * @param \Youshido\GraphQL\Parser\Ast\Interfaces\FieldInterface $ast
   * @param null $parentValue
   *
   * @return mixed|null
   */
  protected function doResolveValue(FieldInterface $field, AstFieldInterface $ast, $parentValue = NULL) {
    $arguments = $this->parseArgumentsValues($field, $ast);
    $astFields = $ast instanceof AstQuery ? $ast->getFields() : [];
    $resolveInfo = $this->createResolveInfo($field, $astFields);

    if ($field instanceof ContainerAwareInterface) {
      $field->setContainer($this->container);
    }

    return $field->resolve($parentValue, $arguments, $resolveInfo);
  }
}
