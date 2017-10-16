<?php

namespace Drupal\graphql_enum_test\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * A number field that returns a number with enum checking.
 *
 * @GraphQLField(
 *   id = "numbers",
 *   secure = true,
 *   name = "numbers",
 *   type = "Numbers",
 *   multi = true,
 *   arguments = {
 *     "numbers" = {
 *       "type" = "Numbers",
 *       "multi" = true,
 *     }
 *   }
 * )
 */
class Numbers extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    foreach ($args['numbers'] as $number) {
      yield $number;
    }
  }

}
