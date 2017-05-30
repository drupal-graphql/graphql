<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\Language\Language;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * The language enum value.
 *
 * @GraphQLField(
 *   id = "language_argument",
 *   name = "argument",
 *   description = "The language id prepared as a language enum value.",
 *   type = "String",
 *   types = {"Language"}
 * )
 */
class LanguageArgument extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Language) {
      yield str_replace('-', '_', $value->getId());
    }
  }

}
