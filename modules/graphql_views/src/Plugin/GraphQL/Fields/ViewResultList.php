<?php

namespace Drupal\graphql_views\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Expose results of a view.
 *
 * @GraphQLField(
 *   id = "view_result",
 *   name = "results",
 *   secure = true,
 *   multi = true,
 *   deriver = "Drupal\graphql_views\Plugin\Deriver\ViewResultListDeriver"
 * )
 */
class ViewResultList extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if (isset($value['rows'])) {
      foreach ($value['rows'] as $row) {
        yield $row;
      }
    }
  }

}
