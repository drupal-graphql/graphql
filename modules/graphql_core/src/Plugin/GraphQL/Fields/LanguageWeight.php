<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\Language\Language;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a language's weight.
 *
 * @GraphQLField(
 *   id = "language_weight",
 *   name = "weight",
 *   type = "Int",
 *   types = {"Language"}
 * )
 */
class LanguageWeight extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Language) {
      yield $value->getWeight();
    }
  }

}
