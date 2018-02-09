<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\EntityReference;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql_core\Plugin\GraphQL\Fields\EntityQuery\EntityQuery;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "entity_reference_reverse",
 *   secure = true,
 *   type = "EntityQueryResult!",
 *   arguments = {
 *     "offset" = {
 *       "type" = "Int",
 *       "default" = 0
 *     },
 *     "limit" = {
 *       "type" = "Int",
 *       "default" = 10
 *     }
 *   },
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityReferenceReverseDeriver"
 * )
 */
class EntityReferenceReverse extends EntityQuery {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof EntityInterface) {
      $definition = $this->getPluginDefinition();
      $field = $definition['field'];

      // Add the target field condition to the query.
      $query = $this->getQuery($value, $args, $info);
      $query->condition($field, $value->id());

      yield $query;
    }
  }

}
