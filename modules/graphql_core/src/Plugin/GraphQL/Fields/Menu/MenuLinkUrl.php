<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Menu;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a menu links route object.
 *
 * @GraphQLField(
 *   id = "menu_link_url",
 *   secure = true,
 *   name = "url",
 *   type = "Url",
 *   parents = {"MenuLink"}
 * )
 */
class MenuLinkUrl extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof MenuLinkTreeElement) {
      yield $value->link->getUrlObject();
    }
  }

}
