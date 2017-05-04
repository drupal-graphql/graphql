<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\Language\Language;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Whether the language is the default language.
 *
 * @GraphQLField(
 *   id = "language_is_default",
 *   name = "isDefault",
 *   type = "Boolean",
 *   types = {"Language"}
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
