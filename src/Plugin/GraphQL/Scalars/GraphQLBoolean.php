<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaManagerInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\PluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Youshido\GraphQL\Type\Scalar\BooleanType;

/**
 * Scalar boolean type.
 *
 * @GraphQLScalar(
 *   id = "boolean",
 *   name = "Boolean",
 *   data_type = "boolean"
 * )
 */
class GraphQLBoolean extends BooleanType implements TypeSystemPluginInterface {
  use PluginTrait;
  use CacheablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public function buildConfig(PluggableSchemaManagerInterface $schemaManager) {
    // Nothing to do here.
  }
}
