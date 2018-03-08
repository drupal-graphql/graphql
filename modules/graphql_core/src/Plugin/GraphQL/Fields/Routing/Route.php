<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Retrieve a route object based on a path.
 *
 * @GraphQLField(
 *   id = "url_route",
 *   secure = true,
 *   name = "route",
 *   description = @Translation("Loads a route by its path."),
 *   type = "Url",
 *   arguments = {
 *     "path" = "String!"
 *   }
 * )
 */
class Route extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if (UrlHelper::isExternal($args['path'])) {
      yield Url::fromUri($args['path']);
    }
    else {
      $url = Url::fromUri("internal:{$args['path']}", ['routed_path' => $args['path']]);
      if ($url->isRouted() && $url->access()) {
        yield $url;
      }
      else {
        yield (new CacheableValue(NULL))->addCacheTags(['4xx-response']);
      }
    }
  }

}
