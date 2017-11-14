<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Menu;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a menu links children.
 *
 * @GraphQLField(
 *   id = "menu_link_links",
 *   secure = true,
 *   name = "links",
 *   type = "MenuLink",
 *   multi = true,
 *   parents = {"MenuLink"}
 * )
 */
class MenuLinkLinks extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof MenuLinkTreeElement) {
      $items = array_filter($value->subtree, function(MenuLinkTreeElement $item) {
        if ($item->link instanceof MenuLinkInterface) {
          return $item->link->isEnabled();
        }
        return TRUE;
      });

      foreach ($items as $branch) {
        yield $branch;
      }
    }
  }

}
