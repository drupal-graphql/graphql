<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaManagerInterface;
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
  public function buildConfig(PluggableSchemaManagerInterface $schemaManager) {
    // Nothing to do here.
  }
}
