<?php

namespace Drupal\graphql_config\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * Plugin for GraphQL types derived from Drupal entity bundles.
 *
 * @GraphQLType(
 *   id = "config_entity_type",
 *   weight = -1,
 *   cache_tags = {"entity_types"},
 *   interfaces = {"Entity"},
 *   deriver = "Drupal\graphql_config\Plugin\Deriver\EntityTypeDeriver"
 * )
 */
class ConfigEntityType extends TypePluginBase {

}
