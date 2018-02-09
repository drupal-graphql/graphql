<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Languages;

use Drupal\Core\Language\Language;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * The language enum value.
 *
 * @GraphQLField(
 *   id = "language_argument",
 *   secure = true,
 *   name = "argument",
 *   description = @Translation("The language id prepared as a language enum value."),
 *   type = "String",
 *   parents = {"Language"}
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
