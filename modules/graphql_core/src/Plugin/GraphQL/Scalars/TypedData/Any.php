<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Scalars\TypedData;

use Drupal\graphql\Plugin\GraphQL\Scalars\ScalarPluginBase;

/**
 * @GraphQLScalar(
 *   id = "any",
 *   name = "Any",
 *   type = "any",
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Scalars\TypedDataPrimitiveFallback"
 * )
 */
class Any extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function serialize($value) {
    if (is_scalar($value)) {
      return $value;
    }

    if (is_array($value)) {
      return json_encode($value);
    }

    if (is_object($value) && method_exists($value, '__toString')) {
      return (string) $value;
    }

    return '';
  }

  /**
   * {@inheritdoc}
   */
  public static function parseValue($value) {
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public static function parseLiteral($ast) {
    return $ast->value;
  }

}
