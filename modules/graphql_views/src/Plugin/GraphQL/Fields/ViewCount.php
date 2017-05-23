<?php

namespace Drupal\graphql_views\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\views\ViewExecutable;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Expose views as root fields.
 *
 * @GraphQLField(
 *   id = "view_count",
 *   name = "count",
 *   nullable = false,
 *   type = "Int"
 * )
 */
class ViewCount extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ViewExecutable) {
      yield intval($value->total_rows);
    }
  }

}
