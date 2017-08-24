<?php

namespace Drupal\graphql_views\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\views\ViewExecutable;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Expose result count of a view.
 *
 * @GraphQLField(
 *   id = "view_result_count",
 *   name = "count",
 *   secure = true,
 *   nullable = false,
 *   type = "Int",
 *   deriver = "Drupal\graphql_views\Plugin\Deriver\ViewResultCountDeriver"
 * )
 */
class ViewResultCount extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ViewExecutable) {
      yield intval($value->total_rows);
    }
  }

}
