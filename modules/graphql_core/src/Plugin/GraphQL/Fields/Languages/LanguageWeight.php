<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Languages;

use Drupal\Core\Language\LanguageInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

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
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof LanguageInterface) {
      yield $value->getWeight();
    }
  }

}
