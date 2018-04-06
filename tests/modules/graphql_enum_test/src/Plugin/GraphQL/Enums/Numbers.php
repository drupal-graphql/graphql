<?php

namespace Drupal\graphql_enum_test\Plugin\GraphQL\Enums;

use Drupal\graphql\Plugin\GraphQL\Enums\EnumPluginBase;
use Drupal\graphql\Plugin\SchemaBuilder;

/**
 * Generates an enumeration of numbers.
 *
 * @GraphQLEnum(
 *   id = "numbers",
 *   name = "Numbers"
 * )
 */
class Numbers extends EnumPluginBase {

  /**
   * A constant list of numbers.
   *
   * @var string[]
   */
  public static $NUMBERS = [
    'zero',
    'one',
    'two',
    'three',
    'four',
    'five',
    'six',
    'seven',
    'eight',
    'nine',
  ];

  /**
   * {@inheritdoc}
   */
  public function buildEnumValues($definition) {
    $values = [];
    foreach (static::$NUMBERS as $num => $word) {
      $values[strtoupper($word)] = [
        'value' => $num,
        'description' => ucfirst($word),
      ];
    }
    return $values;
  }

}
