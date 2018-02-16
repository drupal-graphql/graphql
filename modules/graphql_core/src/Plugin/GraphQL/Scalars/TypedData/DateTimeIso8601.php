<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Scalars\TypedData;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilder;
use Drupal\graphql\Plugin\GraphQL\Scalars\ScalarPluginBase;
use GraphQL\Type\Definition\Type;

/**
 * @GraphQLScalar(
 *   id = "date_time_iso8601",
 *   name = "DateTimeIso8601",
 *   type = "datetime_iso8601"
 * )
 */
class DateTimeIso8601 extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(PluggableSchemaBuilder $builder, $definition, $id) {
    return Type::string();
  }
}
