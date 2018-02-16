<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\EntityReference;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\graphql_core\Plugin\GraphQL\Fields\EntityQuery\EntityQuery;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "entity_reference_reverse",
 *   secure = true,
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
 *     },
 *     "revisions" = {
 *       "type" = "EntityQueryRevisionMode",
 *       "default" = "default"
 *     }
 *   },
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityReferenceReverseDeriver"
 * )
 */
class EntityReferenceReverse extends EntityQuery {

  /**
   * {@inheritdoc}
   */
  public function getBaseQuery($value, array $args, ResolveInfo $info) {
    if ($value instanceof ContentEntityInterface) {
      $query = parent::getBaseQuery($value, $args, $info);

      // Add the target field condition to the query.
      $definition = $this->getPluginDefinition();
      $field = $definition['field'];
      $query->condition($field, $value->id());

      return $query;
    }
  }

}
