<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\EntityReference;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql_core\Plugin\GraphQL\Fields\EntityQuery\EntityQuery;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "entity_reference_query",
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
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityReferenceQueryDeriver"
 * )
 */
class EntityReferenceQuery extends EntityQuery {

  /**
   * {@inheritdoc}
   */
  public function getBaseQuery($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof ContentEntityInterface) {
      $query = parent::getBaseQuery($value, $args, $context, $info);

      // Add the target field condition to the query.
      $definition = $this->getPluginDefinition();
      $key = $definition['entity_key'];
      $field = $definition['field'];
      $ids = array_map(function ($item) {
        return $item['target_id'];
      }, $value->get($field)->getValue());

      if (empty($ids)) {
        return NULL;
      }

      $query->condition($key, $ids);

      return $query;
    }
  }

}
