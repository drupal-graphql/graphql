<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Menu;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Menu link descriptions.
 *
 * @GraphQLField(
 *   id = "menu_link_description",
 *   secure = true,
 *   name = "description",
 *   type = "String",
 *   parents = {"MenuLink"}
 * )
 */
class MenuLinkDescription extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof MenuLinkTreeElement) {
      yield $value->link->getDescription();
    }
  }

}