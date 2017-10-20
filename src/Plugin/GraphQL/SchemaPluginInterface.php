<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;

interface SchemaPluginInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * Retrieves schema cache metadata from the schema.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The schema cache metadata.
   */
  public function getSchemaCacheMetadata();

  /**
   * Retrieves response cache metadata from the schema.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The response cache metadata.
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
