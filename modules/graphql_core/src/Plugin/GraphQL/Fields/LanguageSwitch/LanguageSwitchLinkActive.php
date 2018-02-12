<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\LanguageSwitch;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "language_switch_link_active",
 *   secure = true,
 *   name = "active",
 *   type = "Boolean",
 *   parents = {"LanguageSwitchLink"}
 * )
 */
class LanguageSwitchLinkActive extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    /** @var \Drupal\Core\Language\LanguageInterface $context */
    $context = $value['context'];
    /** @var \Drupal\Core\Language\LanguageInterface $language */
    $language = $value['link']['language'];

    // Check if the link's language code matches the language from the current
    // url context used for retrieving the language switch links.
    yield $context->getId() === $language->getId();
  }

}
