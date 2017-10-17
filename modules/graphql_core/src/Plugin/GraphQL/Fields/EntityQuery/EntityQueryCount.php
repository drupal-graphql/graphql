<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\EntityQuery;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the count of an entity query.
 *
 * @GraphQLField(
 *   id = "entity_query_count",
 *   secure = true,
 *   name = "count",
 *   type = "Int",
 *   parents = {"EntityQueryResult"},
 *   nullable = true
 * )
 */
class EntityQueryCount extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof QueryInterface) {
      // Clone the query and execute it as a count query.
      $clone = clone $value;
      yield (int) $clone->range()->count()->execute();
    }
  }

}
