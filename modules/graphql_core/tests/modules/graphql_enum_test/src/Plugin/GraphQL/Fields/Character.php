<?php

namespace Drupal\graphql_enum_test\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * A number field that returns a number with enum checking.
 *
 * @GraphQLField(
 *   id = "character",
 *   name = "character",
 *   type = {
 *     "a" = "Alpha",
 *     "b" = "Beta",
 *     "c" = "Gamma"
 *   },
 *   arguments = {
 *     "character" = {
 *       "type" = {
 *         "a" = "Alpha",
 *         "b" = "Beta",
 *         "c" = "Gamma"
 *       }
 *     }
 *   }
 * )
 */
class Character extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    yield $args['character'];
  }

}
