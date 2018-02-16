<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Scalars\TypedData;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilder;
use Drupal\graphql\Plugin\GraphQL\Scalars\ScalarPluginBase;
use GraphQL\Type\Definition\Type;

/**
 * @GraphQLScalar(
 *   id = "duration_iso8601",
 *   name = "DurationIso8601",
 *   type = "duration_iso8601"
 * )
 */
class DurationIso8601 extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(PluggableSchemaBuilder $builder, $definition, $id) {
    return Type::string();
  }
}
