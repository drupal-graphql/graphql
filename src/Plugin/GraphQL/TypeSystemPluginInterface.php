<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;

/**
 * Interface for type plugins of all sorts.
 */
interface TypeSystemPluginInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * @return \Drupal\Core\Cache\CacheableMetadata
   */
  public function getSchemaCacheMetadata();

  /**
   * @return \Drupal\Core\Cache\CacheableMetadata
   */
  public function getResponseCacheMetadata();

  /**
   * Plugin config builder method.
   *
   * During the build step, dependencies to other GraphQL plugins are supposed
   * to be resolved.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaManagerInterface $schemaManager
   *   The schema manager to resolve other plugins.
   */
  public function buildConfig(PluggableSchemaManagerInterface $schemaManager);

}
