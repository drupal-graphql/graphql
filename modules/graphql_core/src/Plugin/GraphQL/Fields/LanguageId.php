<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\Language\Language;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a language's id.
 *
 * @GraphQLField(
 *   id = "language_id",
 *   name = "id",
 *   type = "String",
 *   types = {"Language"}
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
