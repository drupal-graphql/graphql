<?php

namespace Drupal\graphql_enum_test\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * A number field that returns a number with enum checking.
 *
 * @GraphQLField(
 *   id = "numbers",
 *   secure = true,
 *   name = "numbers",
 *   type = "[Numbers]",
 *   arguments = {
 *     "numbers" = "[Numbers]"
 *   }
 * )
 */
class Numbers extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    foreach ($args['numbers'] as $number) {
      yield $number;
    }
  }

}
