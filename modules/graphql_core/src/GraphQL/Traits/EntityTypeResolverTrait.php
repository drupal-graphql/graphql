<?php

namespace Drupal\graphql_core\GraphQL\Traits;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;

/**
 * Helper methods to resolve GraphQL type of an Entity.
 */
trait EntityTypeResolverTrait {

  /**
   * Retrieve the matching GraphQL type for an entity.
   *
   * @param \Drupal\graphql_core\GraphQLSchemaManagerInterface $schemaManager
   *   A schema manager instance.
   * @param mixed $entity
   *   The Drupal entity.
   *
   * @return object
   *   The matching GraphQL type plugin or an instance of "HiddenEntity".
   */
  public function resolveEntityType(GraphQLSchemaManagerInterface $schemaManager, $entity) {
    if ($entity instanceof EntityInterface) {
      $type = graphql_core_camelcase([$entity->getEntityTypeId(), $entity->bundle()]);
      try {
        return $schemaManager->findByName($type, [GRAPHQL_CORE_TYPE_PLUGIN]);
      }
      catch (\Exception $exc) {
        return $schemaManager->findByName("UnexposedEntity", [GRAPHQL_CORE_TYPE_PLUGIN]);
      }
    }
    return NULL;
  }

}
