<?php

namespace Drupal\graphql_mutation\Plugin\GraphQL\Mutations;

use Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity\DeleteEntityBase;

/**
 * Delete an entity.
 *
 * @GraphQLMutation(
 *   id = "delete_entity",
 *   type = "EntityCrudOutput",
 *   secure = true,
 *   arguments = {
 *     "id" = "String"
 *   },
 *   nullable = false,
 *   schema_cache_tags = {"entity_types"},
 *   deriver = "Drupal\graphql_mutation\Plugin\Deriver\Mutations\DeleteEntityDeriver"
 * )
 */
class DeleteEntity extends DeleteEntityBase {

}
