<?php

namespace Drupal\graphql_batched_test\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * The users name.
 *
 * @GraphQLField(
 *   id = "name",
 *   secure = true,
 *   name = "name",
 *   parents = {"User"},
 *   type = "String"
 * )
 */
class Name extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    yield $value['name'];
  }

}
