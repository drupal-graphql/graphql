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
 *   type = "EntityQueryResult",
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

      $metadata = $query->getMetadata('graphql_context');
      $ids = $metadata['ids'];
      if (empty($ids)) {
        return NULL;
      }

      $definition = $this->getPluginDefinition();
      $key = $definition['entity_key'];
      $operator = is_array($ids) ? 'IN' : '=';
      $query->condition($key, $ids, $operator);

      return $query;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getQueryContext($value, array $args, ResolveContext $context, ResolveInfo $info) {
    $context = parent::getQueryContext($value, $args, $context, $info);

    // Add the target field condition to the query.
    $definition = $this->getPluginDefinition();
    $field = $definition['field'];
    $ids = array_map(function ($item) {
      return $item['target_id'];
    }, $value->get($field)->getValue());

    return [
      'ids' => $ids,
    ] + $context;
  }

}
