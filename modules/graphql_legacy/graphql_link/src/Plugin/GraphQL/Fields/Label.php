<?php

namespace Drupal\graphql_link\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\link\LinkItemInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a link fields label.
 *
 * @GraphQLField(
 *   id = "link_item_label",
 *   secure = true,
 *   name = "label",
 *   type = "String",
 *   parents = {"LinkItem"}
 * )
 */
class Label extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof LinkItemInterface) {
      yield $value->get('title')->getValue();
    }
  }

}
