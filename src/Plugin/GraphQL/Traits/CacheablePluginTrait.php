<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

use Drupal\Core\Cache\Cache;

/**
 * Methods for GraphQL plugins that are cacheable.
 */
trait CacheablePluginTrait {

  /**
   * {@inheritdoc}
   */
  abstract public function getPluginDefinition();

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $definition = $this->getPluginDefinition();
    return isset($definition['cache_contexts']) ? $definition['cache_contexts'] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $definition = $this->getPluginDefinition();
    return isset($definition['cache_tags']) ? $definition['cache_tags'] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    $definition = $this->getPluginDefinition();
    return isset($definition['cache_max_age']) ? $definition['cache_max_age'] : Cache::PERMANENT;
  }
}
