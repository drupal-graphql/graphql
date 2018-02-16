<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;

/**
 * Interface for type and field plugins of all sorts.
 */
interface TypeSystemPluginInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilder $builder
   * @param $definition
   * @param $id
   *
   * @return mixed
   */
  public static function createInstance(PluggableSchemaBuilder $builder, $definition, $id);

  /**
   * Returns the plugin's type or field definition for the schema.
   *
   * @return array
   *   The type or field definition of the plugin.
   */
  public function getDefinition();

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

}
