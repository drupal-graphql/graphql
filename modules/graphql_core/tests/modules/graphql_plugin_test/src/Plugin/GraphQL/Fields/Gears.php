<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * The number of gears a bike has.
 *
 * @GraphQLField(
 *   id = "gears",
 *   name = "gears",
 *   type = "Int",
 *   types = {"Bike"}
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
