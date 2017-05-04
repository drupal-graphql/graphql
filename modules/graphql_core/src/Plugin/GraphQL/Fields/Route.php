<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\Url;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a route object based on a path.
 *
 * @GraphQLField(
 *   id = "url_route",
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
    $url = Url::fromUri("internal:{$args['path']}", ['routed_path' => $args['path']]);
    if ($url && $url->isRouted() && $url->access()) {
      yield $url;
    }
  }

}
