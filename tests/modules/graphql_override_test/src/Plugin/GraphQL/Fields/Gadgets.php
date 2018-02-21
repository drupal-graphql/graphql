<?php

namespace Drupal\graphql_override_test\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * A bikes gadgets.
 *
 * @GraphQLField(
 *   id = "gadgets",
 *   secure = true,
 *   name = "gadgets",
 *   type = "[String]",
 *   parents = {"Bike"},
 *   weight = 1
 * )
 */
class Gadgets extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    foreach (['Phone charger', 'GPS', 'Coffee machine'] as $gadget) {
      yield $gadget;
    }
  }

}
