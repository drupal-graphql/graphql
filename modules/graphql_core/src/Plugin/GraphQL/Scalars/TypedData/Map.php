<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Scalars\TypedData;

use Drupal\graphql\Plugin\GraphQL\Scalars\ScalarPluginBase;

/**
 * @GraphQLScalar(
 *   id = "map",
 *   name = "Map",
 *   type = "map"
 * )
 */
class Map extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function serialize($value) {
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public static function parseValue($value) {
    return json_decode($value, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public static function parseLiteral($ast) {
    return json_decode($ast->value, TRUE);
  }
}
