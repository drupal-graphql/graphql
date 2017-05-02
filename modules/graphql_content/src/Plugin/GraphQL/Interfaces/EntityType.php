<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Interfaces;

/**
 * Plugin for GraphQL interfaces derived from Drupal entity types.
 *
 * @GraphQLInterface(
 *   id = "entity_type",
 *   weight = -1,
 *   fields = {
 *     "entityId",
 *     "entityUuid",
 *     "entityLabel",
 *     "entityType",
 *     "entityBundle",
 *     "entityUrl"
 *   },
 *   cache_tags = {"entity_types"},
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\EntityTypeDeriver"
 * )
 */
class EntityType extends Entity {

}
