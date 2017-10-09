<?php

namespace Drupal\graphql\GraphQL\Cache;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\ValueWrapperInterface;

/**
 * Wrapper class for values resolved through GraphQL resolvers, which includes
 * Drupal cache metadata.
 *
 * @package Drupal\graphql\GraphQL
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
   *   An array of dependencies, in the order they should be applied.
   */
  public function __construct($value, array $dependencies = []) {
    $this->setValue($value);
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