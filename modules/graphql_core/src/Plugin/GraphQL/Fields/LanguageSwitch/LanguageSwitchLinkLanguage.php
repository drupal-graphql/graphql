<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\LanguageSwitch;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "language_switch_link_language",
 *   secure = true,
 *   name = "language",
 *   type = "Language",
 *   parents = {"LanguageSwitchLink"}
 * )
 */
class LanguageSwitchLinkLanguage extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    /** @var \Drupal\Core\Language\LanguageInterface $language */
    $language = $value['link']['language'];
    yield $language;
  }

}
