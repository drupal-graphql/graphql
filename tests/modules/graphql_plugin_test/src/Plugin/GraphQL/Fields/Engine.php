<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * A cars engine type.
 *
 * @GraphQLField(
 *   id = "engine",
 *   secure = true,
 *   name = "engine",
 *   type = "String",
 *   parents = {"MotorizedVehicle"}
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
