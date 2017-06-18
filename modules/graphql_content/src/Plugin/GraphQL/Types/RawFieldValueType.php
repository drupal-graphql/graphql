<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Types;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * Plugin for GraphQL types derived from raw field values.
 *
 * @GraphQLType(
 *   id = "raw_field_value",
 *   weight = -1,
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\RawFieldValueTypeDeriver"
 * )
 */
class RawFieldValueType extends TypePluginBase {

  /**
   * Returns name of the GraphQL type.
   *
   * @param FieldStorageConfig $storage
   *   Field storage config.
   * @return string
   *   The GraphQL type name.
   */
  public static function getId(FieldStorageConfig $storage) {
    return graphql_core_camelcase([$storage->getTargetEntityTypeId(), $storage->getName(), 'raw_value']);
  }

}
