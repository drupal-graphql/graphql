<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * Plugin for GraphQL types derived from Drupal entity bundles.
 *
 * @GraphQLType(
 *   id = "entity_bundle",
 *   weight = -1,
 *   cache_tags = {"entity_types", "entity_bundles"},
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\EntityBundleDeriver"
 * )
 */
class EntityBundle extends TypePluginBase {

}
