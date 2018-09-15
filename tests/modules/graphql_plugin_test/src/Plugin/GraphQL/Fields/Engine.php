<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

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
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    yield $value['engine'];
  }

}
