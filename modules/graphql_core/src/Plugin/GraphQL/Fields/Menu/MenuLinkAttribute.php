<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Menu;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve specific attributes of a menu link.
 *
 * @GraphQLField(
 *   id = "menu_link_attribute",
 *   secure = true,
 *   name = "attribute",
 *   type = "String",
 *   parents = {"MenuLink"},
 *   nullable = true,
 *   arguments = {
 *     "key" = "String"
 *   }
 * )
 */
class MenuLinkAttribute extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof MenuLinkTreeElement) {
      $options = $value->link->getOptions();
      yield NestedArray::getValue($options, ['attributes', $args['key']]);
    }
  }

}
