<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\LanguageSwitch;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "language_switch_link_title",
 *   secure = true,
 *   name = "title",
 *   type = "String",
 *   parents = {"LanguageSwitchLink"}
 * )
 */
class LanguageSwitchLinkTitle extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    yield $value['link']['title'];
  }

}
