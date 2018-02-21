<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Retrieves the type of vehicle.
 *
 * @GraphQLField(
 *   id = "type",
 *   secure = true,
 *   name = "type",
 *   type = "String",
 *   parents = {"Vehicle"}
 * )
 */
class Type extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    yield $value['type'];
  }

}
