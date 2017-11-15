<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;

/**
 * Interface for type and field plugins of all sorts.
 */
interface TypeSystemPluginInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * Returns the plugin's type or field definition for the schema.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface $schemaBuilder
   *   The schema builder.
   *
   * @return \Youshido\GraphQL\Field\AbstractField|\Youshido\GraphQL\Type\AbstractType
   *   The type or field definition of the plugin.
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder);

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
