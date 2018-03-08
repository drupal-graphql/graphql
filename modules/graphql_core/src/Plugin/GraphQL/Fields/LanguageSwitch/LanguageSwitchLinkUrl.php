<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\LanguageSwitch;

use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

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
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
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
