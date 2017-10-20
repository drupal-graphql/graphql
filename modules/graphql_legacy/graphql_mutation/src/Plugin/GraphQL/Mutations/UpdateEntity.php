<?php

namespace Drupal\graphql_mutation\Plugin\GraphQL\Mutations;

use Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity\UpdateEntityBase;

/**
 * Update an entity.
 *
 * TODO: Add revision support.
 *
 * @GraphQLMutation(
 *   id = "update_entity",
 *   type = "EntityCrudOutput",
 *   secure = true,
 *   nullable = false,
 *   schema_cache_tags = {"entity_types", "entity_bundles"},
 *   deriver = "Drupal\graphql_mutation\Plugin\Deriver\Mutations\UpdateEntityDeriver"
 * )
 */
class UpdateEntity extends UpdateEntityBase {
  use EntityMutationInputTrait;
}
