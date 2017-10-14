<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\GraphQL\SecureFieldInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Exception\ResolveException;
use Youshido\GraphQL\Execution\DeferredResolverInterface;
use Youshido\GraphQL\Execution\Processor as BaseProcessor;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Field\Field;
use Youshido\GraphQL\Field\FieldInterface;
use Youshido\GraphQL\Parser\Ast\Interfaces\FieldInterface as AstFieldInterface;
use Youshido\GraphQL\Parser\Ast\Query as AstQuery;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Type\TypeService;

class Processor extends BaseProcessor {
  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * The cacheable metadata bag.
   *
   * @var \Drupal\Core\Cache\CacheableMetadata
   */
  protected $queryMetadata;

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
    $this->executionContext->getContainer()->set('secure', $secure);
    $this->queryMetadata = new CacheableMetadata();
  }

  /**
   * Return the collected cache metadata from the query.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The collected cache metadata from the query.
   */
  public function getQueryCacheMetadata() {
    $metadata = new CacheableMetadata();
    $metadata->addCacheableDependency($this->queryMetadata);

    // Prevent caching if this is a mutation query.
    if (($request = $this->executionContext->getRequest()) && $request->hasMutations()) {
      $metadata->setCacheMaxAge(0);
    }

    return $metadata;
  }

  /**
   * {@inheritdoc}
   */
  protected function doResolve(FieldInterface $field, AstFieldInterface $ast, $parentValue = NULL) {
    if ($field instanceof SecureFieldInterface) {
      $secure = $this->executionContext->getContainer()->get('secure');

      // If not resolving in a trusted environment, check if the field is secure.
      if (!$secure && !$field->isSecure()) {
        throw new \Exception(sprintf("Unable to resolve insecure field '%s' (%s).", $field->getName(), get_class($field)));
      }
    }

    $value = $this->doResolveValue($field, $ast, $parentValue);
    if ($value instanceof CacheableDependencyInterface) {
      // If the current resolved value returns cache metadata, keep it.
      $this->queryMetadata->addCacheableDependency($value);
    }

    // If it's a GraphQL cacheable value wrapper, extract the real value to return.
    if ($value instanceof CacheableValue) {
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
      $deferredResult = new DeferredResult($this->queryMetadata, $resolvedValue, $callback);
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

    if ($field instanceof Field) {
      if (($resolveFunction = $this->getResolveFunction($field)) && is_callable($resolveFunction)) {
        return $resolveFunction($parentValue, $arguments, $resolveInfo);
      }
      else if ($propertyValue = TypeService::getPropertyValue($parentValue, $field->getName())) {
        return $propertyValue;
      }
    }

    if ($field instanceof ContainerAwareInterface) {
      $field->setContainer($this->container);
    }

    return $field->resolve($parentValue, $arguments, $resolveInfo);
  }

  /**
   * Helper function to resolve a value using a service.
   *
   * @param \Youshido\GraphQL\Field\AbstractField $field
   *
   * @return mixed
   *
   * @throws \Youshido\GraphQL\Exception\ResolveException
   */
  protected function getResolveFunction(AbstractField $field) {
    if ($resolveFunction = $field->getConfig()->getResolveFunction()) {
      if (is_array($resolveFunction) && !is_callable($resolveFunction) && count($resolveFunction) === 2 && strpos($resolveFunction[0], '@') === 0) {
        $service = substr($resolveFunction[0], 1);
        $method = $resolveFunction[1];

        if (!$this->container->has($service)) {
          throw new ResolveException(sprintf('Resolve service "%s" not found for field "%s".', $service, $field->getName()));
        }

        $serviceInstance = $this->container->get($service);
        if (!method_exists($serviceInstance, $method)) {
          throw new ResolveException(sprintf('Resolve method "%s" not found in "%s" service for field "%s".', $method, $service, $field->getName()));
        }

        return [$serviceInstance, $method];
      }
    }

    return $resolveFunction;
  }
}
