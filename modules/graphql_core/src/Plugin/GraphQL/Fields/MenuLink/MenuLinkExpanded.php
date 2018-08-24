<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\MenuLink;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Check if the menu link is expanded.
 *
 * @GraphQLField(
 *   id = "menu_link_expanded",
 *   secure = true,
 *   name = "expanded",
 *   type = "Boolean",
 *   parents = {"MenuLink"}
 * )
 */
class MenuLinkExpanded extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof MenuLinkTreeElement) {
      yield $value->link->isExpanded();
    }
  }

}
