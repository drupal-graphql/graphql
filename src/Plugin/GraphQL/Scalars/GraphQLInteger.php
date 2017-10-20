<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\PluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Youshido\GraphQL\Type\Scalar\IntType;

/**
 * Scalar integer type.
 *
 * @GraphQLScalar(
 *   id = "int",
 *   name = "Int",
 *   data_type = "integer"
 * )
 */
class GraphQLInteger extends IntType implements TypeSystemPluginInterface {
  use PluginTrait;
  use CacheablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public function buildConfig(SchemaBuilderInterface $schemaManager) {
    // Nothing to do here.
  }
}
