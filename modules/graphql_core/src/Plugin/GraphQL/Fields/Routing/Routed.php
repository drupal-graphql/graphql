<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;

use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Check if an Url is routed.
 *
 * @GraphQLField(
 *   id = "url_routed",
 *   secure = true,
 *   name = "routed",
 *   description = @Translation("Boolean indicating whether this is a routed (internal) path."),
 *   type = "Boolean",
 *   parents = {"Url"}
 * )
 */
class Routed extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof Url) {
      yield $value->isRouted();
    }
  }

}
