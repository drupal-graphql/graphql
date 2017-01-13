<?php
/**
 * @file
 * Wrapper class for values resolved through GraphQL resolvers, which includes
 * Drupal cache metadata.
 */

namespace Drupal\graphql\GraphQL;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;

class CacheableGraphqlValue extends CacheableMetadata {

  /**
   * @var mixed The actual value being wrapped.
   */
  protected $value;

  /**
   * CacheableGraphqlValue constructor.
   *
   * @param mixed $value The actual value to be wrapped.
   * @param array $dependencies  An array of dependencies, in the order they
   *   should be applied.
   */
  public function __construct($value, $dependencies = []) {
    $this->setValue($value);
    foreach ($dependencies as $dependency) {
      if ($dependency instanceof CacheableDependencyInterface) {
        $this->addCacheableDependency($dependency);
      }
    }
  }

  public function setValue($value) {
    $this->value = $value;
  }

  public function getValue() {
    return $this->value;
  }

}