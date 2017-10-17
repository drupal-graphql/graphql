<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * The number of gears a bike has.
 *
 * @GraphQLField(
 *   id = "gears",
 *   secure = true,
 *   name = "gears",
 *   type = "Int",
 *   parents = {"Bike"}
 * )
 */
class Gears extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    yield $value['gears'];
  }

}
