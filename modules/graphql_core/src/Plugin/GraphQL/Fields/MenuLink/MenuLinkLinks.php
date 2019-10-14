<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\MenuLink;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Retrieve a menu links children.
 *
 * @GraphQLField(
 *   id = "menu_link_links",
 *   secure = true,
 *   name = "links",
 *   type = "[MenuLink]",
 *   parents = {"MenuLink"}
 * )
 */
class MenuLinkLinks extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof MenuLinkTreeElement) {
      $items = array_filter($value->subtree, function(MenuLinkTreeElement $item) {
        if ($item->link instanceof MenuLinkInterface) {
          return ($item->link->isEnabled() && (empty($item->access) || $item->access->isAllowed()));
        }
        return TRUE;
      });

      foreach ($items as $branch) {
        yield $branch;
      }
    }
  }

}
