<?php

namespace Drupal\graphql_cache_test\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * A field returning a fake object.
 *
 * @GraphQLField(
 *   id = "object",
 *   secure = true,
 *   name = "object",
 *   type = "Object",
 * )
 */
class Object extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    yield new \stdClass();
  }

}
