<?php

namespace Drupal\graphql_example\GraphQL\Field\MenuLink;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\graphql_example\GraphQL\Field\SelfAwareField;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\BooleanType;
use Drupal\link\Plugin\Field\FieldType\LinkItem;

class MenuLinkIsRoutedField extends SelfAwareField {

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($value instanceof MenuLinkInterface) {
      return $value->getUrlObject()->isRouted();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'routed';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new NonNullType(new BooleanType());
  }
}