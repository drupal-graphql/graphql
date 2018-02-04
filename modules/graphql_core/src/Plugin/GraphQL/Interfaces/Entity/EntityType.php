<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Interfaces\Entity;

/**
 * Plugin for GraphQL interfaces derived from Drupal entity types.
 *
 * @GraphQLInterface(
 *   id = "entity_type",
 *   schema_cache_tags = {"entity_types"},
 *   interfaces = {"Entity"},
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Interfaces\EntityTypeDeriver"
 * )
 */
class EntityType extends Entity {

}
