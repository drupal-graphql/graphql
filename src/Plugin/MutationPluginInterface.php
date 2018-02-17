<?php

namespace Drupal\graphql\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;

interface MutationPluginInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * @param \Drupal\graphql\Plugin\SchemaBuilder $builder
   * @param \Drupal\graphql\Plugin\MutationPluginManager $manager
   * @param $definition
   * @param $id
   *
   * @return mixed
   */
  public static function createInstance(SchemaBuilder $builder, MutationPluginManager $manager, $definition, $id);

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
