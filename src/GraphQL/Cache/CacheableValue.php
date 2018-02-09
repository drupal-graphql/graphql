<?php

namespace Drupal\graphql\GraphQL\Cache;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\ValueWrapperInterface;

/**
 * Wrapper class for transporting cache metadata for resolved values.
 *
 * In some cases, especially when resolving leaf (scalar / null) values, the
 * yielded values can't transport any cache metadata themselves. In these cases,
 * you can use this wrapper to decorate your resolved values with cache
 * metadata without having to implement a custom class.
 *
 * @see \Drupal\Core\Cache\RefinableCacheableDependencyInterface
 */
class CacheableValue extends CacheableMetadata implements ValueWrapperInterface {

  /**
   * @var mixed
   *   The actual value being wrapped.
   */
  protected $value;

  /**
   * CacheableValue constructor.
   *
   * @param mixed $value
   *   The actual value to be wrapped.
   * @param array $dependencies
   *   An array of cache dependencies.
   */
  public function __construct($value, array $dependencies = []) {
    $this->value = $value;

    if ($value instanceof CacheableDependencyInterface) {
      $this->addCacheableDependency($value);
    }

    foreach ($dependencies as $dependency) {
      if ($dependency instanceof CacheableDependencyInterface) {
        $this->addCacheableDependency($dependency);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($value) {
    $this->value = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    return $this->value;
  }

}