<?php

namespace Drupal\graphql_menu\Plugin\GraphQL\Fields;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Menu link descriptions.
 *
 * @GraphQLField(
 *   id = "menu_link_description",
 *   name = "description",
 *   type = "String",
 *   types = {"MenuLink"}
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