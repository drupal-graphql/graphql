<?php

namespace Drupal\graphql_override_test\Plugin\GraphQL\Fields;

use Drupal\graphql_plugin_test\Plugin\GraphQL\Fields\Wheels;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Replace existing "wheels" field with ... one more wheel.
 *
 * Affects only bikes, not cars. A car with one more wheel would be ridiculous.
 *
 * @GraphQLField(
 *   id = "more_wheels",
 *   secure = true,
 *   name = "wheels",
 *   type = "Int",
 *   parents = {"Bike"},
 *   weight = 1
 * )
 */
class MoreWheels extends Wheels {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    foreach (parent::resolveValues($value, $args, $info) as $wheels) {
      yield $wheels + 1;
    }
  }

}
