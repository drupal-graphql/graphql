<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "language_switch_link_langcode",
 *   secure = true,
 *   name = "langcode",
 *   type = "String",
 *   types = {"LanguageSwitchLink"}
 * )
 */
class LanguageSwitchLinkLangcode extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    yield $value['langcode'];
  }

}
