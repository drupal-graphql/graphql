<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Languages;

use Drupal\Core\Language\LanguageInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Whether the language is the default language.
 *
 * @GraphQLField(
 *   id = "language_is_default",
 *   secure = true,
 *   name = "isDefault",
 *   description = @Translation("Boolean indicating if this is the configured default language."),
 *   type = "Boolean",
 *   parents = {"Language"}
 * )
 */
class LanguageIsDefault extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof LanguageInterface) {
      yield $value->isDefault();
    }
  }

}
