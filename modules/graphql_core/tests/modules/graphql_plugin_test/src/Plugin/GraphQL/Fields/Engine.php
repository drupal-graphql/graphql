<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * A cars engine type.
 *
 * @GraphQLField(
 *   id = "engine",
 *   name = "engine",
 *   type = "String",
 *   types = {"Car", "CarInput"}
 * )
 */
class Engine extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    yield $value['engine'];
  }

}
