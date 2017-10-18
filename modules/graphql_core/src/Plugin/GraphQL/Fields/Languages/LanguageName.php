<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Languages;

use Drupal\Core\Language\Language;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a language's name.
 *
 * @GraphQLField(
 *   id = "language_name",
 *   secure = true,
 *   name = "name",
 *   type = "String",
 *   parents = {"Language"}
 * )
 */
class LanguageName extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Language) {
      yield $value->getName();
    }
  }

}
