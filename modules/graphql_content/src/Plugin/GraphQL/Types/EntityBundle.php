<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * Plugin for GraphQL interfaces derived from Drupal entity types.
 *
 * @GraphQLType(
 *   id = "entity_bundle",
 *   weight = -1,
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\EntityBundleDeriver"
 * )
 */
class EntityBundle extends TypePluginBase {

}
