<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\Url;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Check if an Url is routed.
 *
 * @GraphQLField(
 *   id = "url_routed",
 *   name = "routed",
 *   type = "Boolean"
 * )
 */
class Routed extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Url) {
      yield $value->isRouted();
    }
  }

}
