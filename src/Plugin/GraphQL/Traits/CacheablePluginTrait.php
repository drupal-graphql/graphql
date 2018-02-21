<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

use Drupal\Core\Cache\CacheBackendInterface;

trait CacheablePluginTrait {

  /**
   * @param $definition
   *
   * @return array
   */
  protected function buildCacheMetadata($definition) {
    return [
      'schema_cache_contexts' => isset($definition['schema_cache_contexts']) ? $definition['schema_cache_contexts'] : ['languages:language_interface'],
      'schema_cache_tags' => isset($definition['schema_cache_tags']) ? $definition['schema_cache_tags'] : [],
      'schema_cache_max_age' => isset($definition['schema_cache_max_age']) ? $definition['schema_cache_max_age'] : CacheBackendInterface::CACHE_PERMANENT,
      'response_cache_contexts' => isset($definition['response_cache_contexts']) ? $definition['response_cache_contexts'] : ['user.permissions'],
      'response_cache_tags' => isset($definition['response_cache_tags']) ? $definition['response_cache_tags'] : [],
      'response_cache_max_age' => isset($definition['response_cache_max_age']) ? $definition['response_cache_max_age'] : CacheBackendInterface::CACHE_PERMANENT,
    ];
  }
}
