<?php

namespace Drupal\graphql_cache_test;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;

/**
 * Cache context ID: 'graphql_test'.
 */
class TestCacheContext implements CacheContextInterface {

  /**
   * An arbitrary context value.
   *
   * @var int
   */
  protected $value = 0;

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('GraphQL test context');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    return $this->value;
  }

  /**
   * Set the context value.
   *
   * @param int $value
   *   The new context value.
   */
  public function setContext($value) {
    $this->value = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
