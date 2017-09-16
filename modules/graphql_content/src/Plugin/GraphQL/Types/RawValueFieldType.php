<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Types;

use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * Plugin for GraphQL types derived from raw field values.
 *
 * @GraphQLType(
 *   id = "raw_field_value",
 *   weight = -1,
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\RawValueFieldTypeDeriver",
 *   field_formatter = "graphql_raw_value"
 * )
 */
class RawValueFieldType extends TypePluginBase {

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
    return StringHelper::camelCase([$entityTypeId, $fieldName, 'raw_value']);
  }

}
