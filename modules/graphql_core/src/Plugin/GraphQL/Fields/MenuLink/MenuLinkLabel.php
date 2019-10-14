<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\MenuLink;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Retrieve a menu links label.
 *
 * @GraphQLField(
 *   id = "menu_link_label",
 *   secure = true,
 *   name = "label",
 *   type = "String",
 *   parents = {"MenuLink"},
 *   response_cache_contexts = {"languages:language_interface"}
 * )
 */
class MenuLinkLabel extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof MenuLinkTreeElement) {
      yield $value->link->getTitle();
    }
  }

}
