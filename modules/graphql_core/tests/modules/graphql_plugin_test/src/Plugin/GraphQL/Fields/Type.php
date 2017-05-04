<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieves the type of vehicle.
 *
 * @GraphQLField(
 *   id = "type",
 *   name = "type",
 *   type = "String",
 *   types = {"Bike", "Car"}
 * )
 */
class Type extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    yield $value['type'];
  }

}
