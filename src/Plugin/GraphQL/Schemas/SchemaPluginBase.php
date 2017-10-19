<?php

namespace Drupal\graphql\Plugin\GraphQL\Schemas;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\Utility\TypeCollector;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\PluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Youshido\GraphQL\Config\Schema\SchemaConfig;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Type\InputObject\AbstractInputObjectType;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

abstract class SchemaPluginBase extends AbstractSchema implements SchemaPluginInterface {

  use PluginTrait;

  /**
   * The response cache metadata object.
   *
   * @var \Drupal\Core\Cache\CacheableMetadata
   */
  protected $responseMetadata;

  /**
   * The schema cache metadata object.
   *
   * @var \Drupal\Core\Cache\CacheableMetadata
   */
  protected $schemaMetadata;

  /**
   * {@inheritdoc}
   */
  public function __construct($configuration, $pluginId, $pluginDefinition) {
    $this->constructPlugin($configuration, $pluginId, $pluginDefinition);
    $this->constructSchema($configuration, $pluginId, $pluginDefinition);
    $this->constructCacheMetadata($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * Constructs the schema configuration.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   */
  abstract protected function constructSchema($configuration, $pluginId, $pluginDefinition);

  /**
   * Constructs the schema cache metadata.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   */
  protected function constructCacheMetadata($configuration, $pluginId, $pluginDefinition) {
    // Build the schema and response metadata objects based on the provided
    // schema config and all included types/fields/etc.
    $this->responseMetadata = new CacheableMetadata();
    $this->responseMetadata->setCacheMaxAge(Cache::PERMANENT);
    $this->schemaMetadata = new CacheableMetadata();
    $this->schemaMetadata->setCacheMaxAge(Cache::PERMANENT);

    foreach (TypeCollector::collectTypes($this) as $type) {
      if ($type instanceof TypeSystemPluginInterface) {
        $this->schemaMetadata->addCacheableDependency($type->getSchemaCacheMetadata());
        $this->responseMetadata->addCacheableDependency($type->getResponseCacheMetadata());
      }

      if ($type instanceof AbstractObjectType || $type instanceof AbstractInputObjectType || $type instanceof AbstractInterfaceType) {
        foreach ($type->getFields() as $field) {
          if ($field instanceof TypeSystemPluginInterface) {
            $this->schemaMetadata->addCacheableDependency($field->getSchemaCacheMetadata());
            $this->responseMetadata->addCacheableDependency($field->getResponseCacheMetadata());
          }
        }
      }
    }

    // Merge the schema cache metadata into the response cache metadata.
    $this->responseMetadata->addCacheableDependency($this->schemaMetadata);
  }

  /**
   * Collects schema cache metadata from all types registered with the schema.
   *
   * The cache metadata is statically cached. This means that the schema may not
   * be modified after this method has been called.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cache metadata collected from the schema's types.
   */
  public function getSchemaCacheMetadata() {
    return $this->schemaMetadata;
  }

  /**
   * Collects result cache metadata from all types registered with the schema.
   *
   * The cache metadata is statically cached. This means that the schema may not
   * be modified after this method has been called.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cache metadata collected from the schema's types.
   */
  public function getResponseCacheMetadata() {
    return $this->responseMetadata;
  }

  /**
   * {@inheritdoc}
   */
  public function build(SchemaConfig $config) {
    // Not needed anymore.
  }

}
