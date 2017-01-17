<?php

namespace Drupal\graphql_example\GraphQL\Field\MenuLink;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\graphql_example\GraphQL\Field\SelfAwareField;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Scalar\BooleanType;

class MenuLinkIsExpandedField extends SelfAwareField {

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($value instanceof MenuLinkInterface) {
      return (bool) $value->isExpanded();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'expanded';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new BooleanType();
  }
}