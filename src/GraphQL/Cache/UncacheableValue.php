<?php

namespace Drupal\graphql\GraphQL\Cache;

/**
 * Wrapper class for uncacheable values resolved through GraphQL resolvers.
 *
 * @package Drupal\graphql\GraphQL
 */
class UncacheableValue extends CacheableValue {

  /**
   * {@inheritdoc}
   */
  protected $cacheMaxAge = 0;

  /**
   * CacheableValue constructor.
   *
   * @param mixed $value
   *   The actual value to be wrapped.
   */
  public function __construct($value) {
    parent::__construct($value);
  }

}