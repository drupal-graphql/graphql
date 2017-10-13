<?php

namespace Drupal\graphql_enum_test\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * A number field that returns a number with enum checking.
 *
 * @GraphQLField(
 *   id = "number",
 *   secure = true,
 *   name = "number",
 *   type = "Numbers",
 *   arguments = {
 *     "number" = "Numbers"
 *   }
 * )
 */
class Number extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    yield $args['number'];
  }

}
