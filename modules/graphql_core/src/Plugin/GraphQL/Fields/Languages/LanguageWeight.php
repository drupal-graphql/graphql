<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Languages;

use Drupal\Core\Language\Language;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a language's weight.
 *
 * @GraphQLField(
 *   id = "language_weight",
 *   secure = true,
 *   name = "weight",
 *   description = @Translation("The weight of the language."),
 *   type = "Int",
 *   parents = {"Language"}
 * )
 */
class LanguageWeight extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Language) {
      yield $value->getWeight();
    }
  }

}
