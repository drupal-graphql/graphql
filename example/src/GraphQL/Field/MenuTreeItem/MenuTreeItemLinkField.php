<?php

namespace Drupal\graphql_example\GraphQL\Field\MenuTreeItem;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql_example\GraphQL\Field\SelfAwareField;
use Drupal\graphql_example\GraphQL\Type\MenuLinkType;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\NonNullType;

class MenuTreeItemLinkField extends SelfAwareField {

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($value instanceof MenuLinkTreeElement) {
      return $value->link;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'link';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new NonNullType(new MenuLinkType());
  }
}