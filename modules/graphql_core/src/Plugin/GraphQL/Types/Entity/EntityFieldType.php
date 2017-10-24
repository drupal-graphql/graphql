<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Entity;

use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;

// TODO Add cache tags.

/**
 * Plugin for GraphQL types derived from raw field values.
 *
 * @GraphQLType(
 *   id = "entity_field_value",
 *   weight = -1,
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Types\EntityFieldTypeDeriver",
 * )
 */
class EntityFieldType extends TypePluginBase {

  /**
   * Returns name of the GraphQL type.
   *
   * @param string $entityTypeId
   *   Entity type id.
   * @param string $fieldName
   *   Field name.
   * @return string
   *   The GraphQL type name.
   */
  public static function getId($entityTypeId, $fieldName) {
    $result = StringHelper::camelCase('field', $entityTypeId, $fieldName);
    return $result;
  }

}
