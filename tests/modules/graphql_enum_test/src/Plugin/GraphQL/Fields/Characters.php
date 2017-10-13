<?php

namespace Drupal\graphql_enum_test\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * A number field that returns a number with enum checking.
 *
 * @GraphQLField(
 *   id = "characters",
 *   name = "characters",
 *   multi = true,
 *   secure = true,
 *   enum_type_name = "CharactersEnum",
 *   type = {
 *     "a" = "Alpha",
 *     "b" = "Beta",
 *     "c" = "Gamma"
 *   },
 *   arguments = {
 *     "characters" = {
 *       "enum_type_name" = "CharactersEnum",
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
