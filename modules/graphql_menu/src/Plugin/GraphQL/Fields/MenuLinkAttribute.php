<?php

namespace Drupal\graphql_menu\Plugin\GraphQL\Fields;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve specific attributes of a menu link.
 *
 * @GraphQLField(
 *   id = "menu_link_attribute",
 *   name = "attribute",
 *   type = "String",
 *   types = {"MenuLink"},
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
