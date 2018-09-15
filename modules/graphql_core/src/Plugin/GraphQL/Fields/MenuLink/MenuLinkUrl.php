<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\MenuLink;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

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
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof MenuLinkTreeElement) {
      yield $value->link->getUrlObject();
    }
  }

}
