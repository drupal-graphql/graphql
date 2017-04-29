<?php

namespace Drupal\graphql_menu\Plugin\GraphQL\Fields;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a menu links children.
 *
 * @GraphQLField(
 *   id = "menu_link_links",
 *   name = "links",
 *   type = "MenuLink",
 *   multi = true,
 *   types = {"MenuLink"}
 * )
 */
class MenuLinkLinks extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof MenuLinkTreeElement) {
      foreach (array_filter($value->subtree, function (MenuLinkTreeElement $item) {
        if ($item->link instanceof MenuLinkInterface) {
          return $item->link->isEnabled();
        }
        return TRUE;
      }) as $branch) {
        yield $branch;
      }
    }
  }

}
