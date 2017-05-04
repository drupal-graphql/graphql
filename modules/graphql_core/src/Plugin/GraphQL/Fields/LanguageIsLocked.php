<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\Language\Language;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Whether the language is locked.
 *
 * @GraphQLField(
 *   id = "language_is_locked",
 *   name = "isLocked",
 *   type = "Boolean",
 *   types = {"Language"}
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
