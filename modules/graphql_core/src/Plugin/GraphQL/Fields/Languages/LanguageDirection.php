<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Languages;

use Drupal\Core\Language\Language;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a language's name.
 *
 * @GraphQLField(
 *   id = "language_direction",
 *   secure = true,
 *   name = "direction",
 *   description = @Translation("The language direction (rtl or ltr)."),
 *   type = "String",
 *   parents = {"Language"}
 * )
 */
class LanguageDirection extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Language) {
      yield $value->getDirection();
    }
  }

}
