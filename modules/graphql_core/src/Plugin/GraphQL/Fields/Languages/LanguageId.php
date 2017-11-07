<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Languages;

use Drupal\Core\Language\Language;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a language's id.
 *
 * @GraphQLField(
 *   id = "language_id",
 *   secure = true,
 *   name = "id",
 *   description = @Translation("The language id."),
 *   type = "String",
 *   parents = {"Language"}
 * )
 */
class LanguageId extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Language) {
      yield $value->getId();
    }
  }

}
