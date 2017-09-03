<?php

namespace Drupal\graphql_cache_test;

/**
 * Cache context ID: 'graphql_test_root_field'.
 */
class TestRootFieldCacheContext extends TestCacheContext {

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('GraphQL test context only for root fields');
  }
}
