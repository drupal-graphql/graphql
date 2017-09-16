<?php

namespace Drupal\graphql_link\Plugin\GraphQL\Fields;

use Drupal\Component\Utility\NestedArray;
use Drupal\link\LinkItemInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve specific attributes of a menu link.
 *
 * @GraphQLField(
 *   id = "link_item_attribute",
 *   secure = true,
 *   name = "attribute",
 *   type = "String",
 *   types = {"LinkItem"},
 *   nullable = true,
 *   arguments = {
 *     "key" = "String"
 *   }
 * )
 */
class LinkAttribute extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof LinkItemInterface) {
      $options = $value->getUrl()->getOptions();
      yield NestedArray::getValue($options, ['attributes', $args['key']]);
    }
  }

}
