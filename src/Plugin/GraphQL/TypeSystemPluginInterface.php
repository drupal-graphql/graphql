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
   * @param \Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface $schemaBuilder
   *   The schema builder.
   */
  public function buildConfig(SchemaBuilderInterface $schemaBuilder);

}
