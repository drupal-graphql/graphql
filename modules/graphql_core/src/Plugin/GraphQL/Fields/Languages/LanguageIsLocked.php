<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Languages;

use Drupal\Core\Language\Language;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Whether the language is locked.
 *
 * @GraphQLField(
 *   id = "language_is_locked",
 *   secure = true,
 *   name = "isLocked",
 *   type = "Boolean",
 *   parents = {"Language"}
 * )
 */
class LanguageIsLocked extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Language) {
      yield $value->isLocked();
    }
  }

}
