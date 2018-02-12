<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;

use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "url_as_absolute_url",
 *   secure = true,
 *   name = "asAbsoluteUrl",
 *   type = "Url",
 *   types = {"Url"}
 * )
 */
class UrlAsAbsoluteUrl extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Url) {
      yield $value->setAbsolute(TRUE);
    }
  }

}
