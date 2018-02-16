<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Scalars\TypedData;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilder;
use Drupal\graphql\Plugin\GraphQL\Scalars\ScalarPluginBase;
use GraphQL\Type\Definition\Type;

/**
 * @GraphQLScalar(
 *   id = "uri",
 *   name = "Uri",
 *   type = "uri"
 * )
 */
class Uri extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(PluggableSchemaBuilder $builder, $definition, $id) {
    return Type::string();
  }
}
