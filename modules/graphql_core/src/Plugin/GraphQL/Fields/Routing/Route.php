<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a route object based on a path.
 *
 * @GraphQLField(
 *   id = "url_route",
 *   secure = true,
 *   name = "route",
 *   type = "Url",
 *   nullable = true,
 *   arguments = {
 *     "path" = "String"
 *   }
 * )
 */
class Route extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if (UrlHelper::isExternal($args['path'])) {
      yield Url::fromUri($args['path']);
    }
    else {
      $url = Url::fromUri("internal:{$args['path']}", ['routed_path' => $args['path']]);
      if ($url && $url->isRouted() && $url->access()) {
        yield $url;
      }
    }
  }

}
