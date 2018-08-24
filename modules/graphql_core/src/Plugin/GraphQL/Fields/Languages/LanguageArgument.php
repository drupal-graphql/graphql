<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Languages;

use Drupal\Core\Language\LanguageInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

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
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof LanguageInterface) {
      yield str_replace('-', '_', $value->getId());
    }
  }

}
