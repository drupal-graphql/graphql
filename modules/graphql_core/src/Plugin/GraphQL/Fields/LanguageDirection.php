<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\Language\Language;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a language's name.
 *
 * @GraphQLField(
 *   id = "language_direction",
 *   name = "direction",
 *   type = "String",
 *   types = {"Language"}
 * )
 */
class LanguageDirection extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Language) {
      yield $value->getDirection();
    }
  }

}
