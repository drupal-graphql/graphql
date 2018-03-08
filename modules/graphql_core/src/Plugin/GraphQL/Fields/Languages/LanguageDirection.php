<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Languages;

use Drupal\Core\Language\LanguageInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

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
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof LanguageInterface) {
      yield $value->getDirection();
    }
  }

}
