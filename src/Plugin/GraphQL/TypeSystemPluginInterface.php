<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;

/**
 * Interface for type plugins of all sorts.
 */
interface TypeSystemPluginInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * Returns the cache metadata affecting the schema.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cache metadata affecting the schema.
   */
  public function getSchemaCacheMetadata();

  /**
   * Returns the cache metadata affecting the response.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cache metadata affecting the response.
   */
  public function getResponseCacheMetadata();

  /**
   * Plugin config builder method.
   *
   * During the build step, dependencies to other GraphQL plugins are supposed
   * to be resolved.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\SchemaBuilder $schemaManager
   *   The schema manager to resolve other plugins.
   */
  public function buildConfig(SchemaBuilder $schemaManager);

}
