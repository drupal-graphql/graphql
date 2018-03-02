<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

/**
 * Trait CacheablePluginTrait
 *
 * @package Drupal\graphql\Plugin\GraphQL\Traits
 */
trait CacheablePluginTrait {

  /**
   * @param $definition
   *
   * @return array
   */
  protected function buildCacheContexts($definition) {
    $schema = isset($definition['schema_cache_contexts']) ? $definition['schema_cache_contexts'] : ['languages:language_interface'];
    $response = isset($definition['response_cache_contexts']) ? $definition['response_cache_contexts'] : ['user.permissions'];
    return array_unique(array_merge($schema, $response));
  }

}
