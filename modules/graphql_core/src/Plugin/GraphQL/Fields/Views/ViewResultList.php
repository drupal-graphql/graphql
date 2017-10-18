<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Views;

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
 *   provider = "views",
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\ViewResultListDeriver"
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
