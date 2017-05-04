<?php

namespace Drupal\graphql_enum_test\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * A number field that returns a number with enum checking.
 *
 * @GraphQLField(
 *   id = "characters",
 *   name = "characters",
 *   multi = true,
 *   type = {
 *     "a" = "Alpha",
 *     "b" = "Beta",
 *     "c" = "Gamma"
 *   },
 *   arguments = {
 *     "characters" = {
 *       "multi" = true,
 *       "type" = {
 *         "a" = "Alpha",
 *         "b" = "Beta",
 *         "c" = "Gamma"
 *       }
 *     }
 *   }
 * )
 */
class Characters extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    foreach ($args['characters'] as $character) {
      yield $character;
    }
  }

}
