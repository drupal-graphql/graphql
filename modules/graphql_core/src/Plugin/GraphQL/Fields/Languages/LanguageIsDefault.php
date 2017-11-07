<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Languages;

use Drupal\Core\Language\Language;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

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
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Language) {
      yield $value->isDefault();
    }
  }

}
