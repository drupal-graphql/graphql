<?php

namespace Drupal\graphql_menu\Plugin\GraphQL\Fields;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a menu links label.
 *
 * @GraphQLField(
 *   id = "menu_link_label",
 *   secure = true,
 *   name = "label",
 *   type = "String",
 *   types = {"MenuLink"}
 * )
 */
class MenuLinkLabel extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof MenuLinkTreeElement) {
      yield $value->link->getTitle();
    }
  }

}
