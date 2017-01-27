<?php

namespace Drupal\graphql\GraphQL;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Class CacheableLeafValue
 *
 * Wrapper class for values resolved through GraphQL resolvers, which includes
 * Drupal cache metadata.
 *
 * @package Drupal\graphql\GraphQL
 */
class CacheableLeafValue extends CacheableMetadata {

  /**
   * @var mixed
   *   The actual value being wrapped.
   */
  protected $value;

  /**
   * CacheableLeafValue constructor.
   *
   * @param mixed $value
   *   The actual value to be wrapped.
   * @param array $dependencies
   *   An array of dependencies, in the order they should be applied.
   */
  public function __construct($value, $dependencies = []) {
    $this->setValue($value);
    foreach ($dependencies as $dependency) {
      if ($dependency instanceof CacheableDependencyInterface) {
        $this->addCacheableDependency($dependency);
      }
    }
  }

  /**
   * Set the wrapped value.
   *
   * @param mixed $value
   */
  public function setValue($value) {
    $this->value = $value;
  }

  /**
   * Get the wrapped value.
   *
   * @return mixed
   */
  public function getValue() {
    return $this->value;
  }

}