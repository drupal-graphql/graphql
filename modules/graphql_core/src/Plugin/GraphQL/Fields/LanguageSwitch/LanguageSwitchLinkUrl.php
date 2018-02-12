<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\LanguageSwitch;

use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "language_switch_link_url",
 *   secure = true,
 *   name = "url",
 *   type = "InternalUrl",
 *   parents = {"LanguageSwitchLink"}
 * )
 */
class LanguageSwitchLinkUrl extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    /** @var \Drupal\Core\Language\LanguageInterface $language */
    $language = $value['link']['language'];

    /** @var \Drupal\Core\Url $url */
    $url = $value['link']['url'];
    $url = Url::fromRoute($url->getRouteName(), $url->getRouteParameters(), [
      'language' => $language,
    ] + $url->getOptions());

    yield $url;
  }

}
