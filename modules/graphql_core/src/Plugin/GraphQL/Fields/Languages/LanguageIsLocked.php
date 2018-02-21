<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Languages;

use Drupal\Core\Language\LanguageInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Whether the language is locked.
 *
 * @GraphQLField(
 *   id = "language_is_locked",
 *   secure = true,
 *   name = "isLocked",
 *   description = @Translation("Boolean indicating if this language is locked."),
 *   type = "Boolean",
 *   parents = {"Language"}
 * )
 */
class LanguageIsLocked extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof LanguageInterface) {
      yield $value->isLocked();
    }
  }

}
