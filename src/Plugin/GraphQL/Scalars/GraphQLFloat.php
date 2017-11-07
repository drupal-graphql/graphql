<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\PluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Youshido\GraphQL\Type\Scalar\FloatType;

/**
 * Scalar float type.
 *
 * @GraphQLScalar(
 *   id = "float",
 *   name = "Float",
 *   data_type = "float"
 * )
 */
class GraphQLFloat extends FloatType implements TypeSystemPluginInterface {
  use PluginTrait;
  use CacheablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition) {
    $this->constructPlugin($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfig(SchemaBuilderInterface $schemaManager) {
    // Nothing to do here.
  }
}
