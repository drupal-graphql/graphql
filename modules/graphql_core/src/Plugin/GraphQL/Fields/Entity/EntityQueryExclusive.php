<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql_core\Plugin\GraphQL\Fields\EntityQuery\EntityQuery;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Query entities of the same type without the context's entity.
 *
 * @GraphQLField(
 *   id = "entity_query_exclusive",
 *   name = "entityQueryExclusive",
 *   secure = true,
 *   type = "EntityQueryResult!",
 *   parents = {"Entity"},
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
 *     },
 *     "bundles" = {
 *       "type" = "EntityQueryBundleMode",
 *       "default" = "same"
 *     }
 *   }
 * )
 */
class EntityQueryExclusive extends EntityQuery {

  /**
   * {@inheritdoc}
   */
  protected function getBaseQuery($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof ContentEntityInterface) {
      $type = $value->getEntityType();
      $id = $type->getKey('id');

      // Filter out the current entity.
      $query = parent::getBaseQuery($value, $args, $context, $info);
      $query->condition($id, $value->id(), '<>');

      if (array_key_exists('bundles', $args)) {
        $query = $this->applyBundleMode($query, $value, $args['bundles']);
      }

      return $query;
    }
  }

  /**
   * Apply the specified bundle filtering mode to the query.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The entity query object.
   * @param \Drupal\Core\Entity\ContentEntityInterface $value
   *   The parent entity object.
   * @param mixed $mode
   *   The revision query mode.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface The entity query object.
   * The entity query object.
   */
  protected function applyBundleMode(QueryInterface $query, ContentEntityInterface $value, $mode) {
    if ($mode === 'same') {
      $type = $value->getEntityType();

      if ($type->hasKey('bundle')) {
        $bundle = $type->getKey('bundle');
        $query->condition($bundle, $value->bundle());
      }
    }

    return $query;
  }

}
