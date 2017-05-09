<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\CacheableValue;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Exception\ResolveException;
use Youshido\GraphQL\Execution\Processor as BaseProcessor;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Field\Field;
use Youshido\GraphQL\Field\FieldInterface;
use Youshido\GraphQL\Parser\Ast\Interfaces\FieldInterface as AstFieldInterface;
use Youshido\GraphQL\Parser\Ast\Query as AstQuery;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Type\TypeService;

class Processor extends BaseProcessor implements CacheableDependencyInterface {
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
  protected $metadata;

  /**
   * Constructs a Processor object.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The dependency injection container.
   * @param \Youshido\GraphQL\Schema\AbstractSchema $schema
   *   The GraphQL schema.
   */
  public function __construct(ContainerInterface $container, AbstractSchema $schema) {
    $this->container = $container;
    $this->metadata = new CacheableMetadata();

    // Add cache metadata from the active schema.
    if ($schema instanceof CacheableDependencyInterface) {
      $this->metadata->addCacheableDependency($schema);
    }

    parent::__construct($schema);
  }

  /**
   * {@inheritdoc}
   */
  protected function doResolve(FieldInterface $field, AstFieldInterface $ast, $parentValue = NULL) {
    $value = $this->doResolveValue($field, $ast, $parentValue);

    if ($value instanceof CacheableDependencyInterface) {
      // If the current resolved value returns cache metadata, keep it.
      $this->metadata->addCacheableDependency($value);
    }

    // If it's a GraphQL cacheable value wrapper, extract the real value to return.
    if ($value instanceof CacheableValue) {
      $value = $value->getValue();
    }

    return $value;
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

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return $this->metadata->getCacheContexts();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return $this->metadata->getCacheTags();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return $this->metadata->getCacheMaxAge();
  }
}
