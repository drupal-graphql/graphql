<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql_core\Plugin\GraphQL\Fields\EntityQuery\EntityQuery;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "entity_revisions",
 *   name = "entityRevisions",
 *   secure = true,
 *   parents = {"EntityRevisionable"},
 *   type = "EntityQueryResult!",
 *   arguments = {
 *     "filter" = "EntityQueryFilterInput",
 *     "sort" = "[EntityQuerySortInput]",
 *     "offset" = {
 *       "type" = "Int",
 *       "default" = 0
 *     },
 *     "limit" = {
 *       "type" = "Int",
 *       "default" = 10
 *     }
 *   }
 * )
 */
class EntityRevisions extends EntityQuery {

  /**
   * {@inheritdoc}
   */
  public function getBaseQuery($value, array $args, ResolveInfo $info) {
    if ($value instanceof EntityInterface) {
      $query = parent::getBaseQuery($value, $args, $info);

      // Add the entity id as a filter condition.
      $key = $value->getEntityType()->getKey('id');
      $query->condition($key, $value->id());

      // Mark the query as a revision query.
      return $this->applyRevisionsMode($query, 'all');
    }
  }

}
