<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Entity;

use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;

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

}
