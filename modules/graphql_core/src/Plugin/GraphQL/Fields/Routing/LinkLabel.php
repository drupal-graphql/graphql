<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;
use Drupal\Core\Link;

/**
 * Retrieve a link text.
 *
 * @GraphQLField(
 *   id = "link_label",
 *   secure = true,
 *   name = "text",
 *   type = "String",
 *   parents = {"Link"}
 * )
 */
class LinkLabel extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Link) {
      yield $value->getText();
    }
  }

}
