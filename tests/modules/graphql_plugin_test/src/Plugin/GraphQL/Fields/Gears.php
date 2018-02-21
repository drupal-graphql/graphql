<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

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
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    yield $value['gears'];
  }

}
