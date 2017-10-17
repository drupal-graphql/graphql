<?php

namespace Drupal\graphql_override_test\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * A bikes gadgets.
 *
 * @GraphQLField(
 *   id = "gadgets",
 *   secure = true,
 *   name = "gadgets",
 *   type = "String",
 *   multi = true,
 *   parents = {"Bike"},
 *   weight = 1
 * )
 */
class Gadgets extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    foreach (['Phone charger', 'GPS', 'Coffee machine'] as $gadget) {
      yield $gadget;
    }
  }

}
