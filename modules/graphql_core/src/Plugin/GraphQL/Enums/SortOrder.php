<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Enums;

use Drupal\graphql_core\GraphQL\EnumPluginBase;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;

/**
 * Generates an enumeration of numbers.
 *
 * @GraphQLEnum(
 *   id = "sort_order",
 *   name = "SortOrder"
 * )
 */
class SortOrder extends EnumPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildValues(GraphQLSchemaManagerInterface $schemaManager) {
    $values = [
      [
        'name' => 'ASC',
        'value' => 'ASC',
      ],
      [
        'name' => 'DESC',
        'value' => 'DESC',
      ],
    ];

    return $values;
  }

}
