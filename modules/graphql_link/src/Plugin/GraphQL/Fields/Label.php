<?php

namespace Drupal\graphql_link\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\link\LinkItemInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a link fields label.
 *
 * @GraphQLField(
 *   id = "link_item_label",
 *   name = "label",
 *   type = "String",
 *   types = {"LinkItem"}
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
