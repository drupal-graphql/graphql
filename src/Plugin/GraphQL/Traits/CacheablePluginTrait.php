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
    if (!empty($definition['response_cache_contexts'])) {
      return $definition['response_cache_contexts'];
    }

    return [];
  }

}
