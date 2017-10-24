<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Methods for GraphQL plugins that are cacheable.
 */
trait CacheablePluginTrait {

  /**
   * {@inheritdoc}
   */
  abstract public function getPluginDefinition();

  /**
   * Collects schema cache metadata for this plugin.
   *
   * The cache metadata is statically cached. This means that the schema may not
   * be modified after this method has been called.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cache metadata collected from the schema's types.
   */
  public function getSchemaCacheMetadata() {
    $definition = $this->getPluginDefinition();
    $metadata = new CacheableMetadata();
    $metadata->addCacheContexts(isset($definition['schema_cache_contexts']) ? $definition['schema_cache_contexts'] : ['languages:language_interface']);
    $metadata->addCacheTags(isset($definition['schema_cache_tags']) ? $definition['schema_cache_tags'] : []);
    $metadata->setCacheMaxAge(isset($definition['schema_cache_max_age']) ? $definition['schema_cache_max_age'] : CacheBackendInterface::CACHE_PERMANENT);
    return $metadata;
  }

  /**
   * Collects result cache metadata for this plugin.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cache metadata collected from the schema's types.
   */
  public function getResponseCacheMetadata() {
    $definition = $this->getPluginDefinition();
    $metadata = new CacheableMetadata();
    $metadata->addCacheContexts(isset($definition['response_cache_contexts']) ? $definition['response_cache_contexts'] : ['user']);
    $metadata->addCacheTags(isset($definition['response_cache_tags']) ? $definition['response_cache_tags'] : []);
    $metadata->setCacheMaxAge(isset($definition['response_cache_max_age']) ? $definition['response_cache_max_age'] : CacheBackendInterface::CACHE_PERMANENT);
    return $metadata;
  }
}
