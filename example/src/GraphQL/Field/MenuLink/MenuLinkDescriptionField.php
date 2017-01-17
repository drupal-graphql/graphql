<?php

namespace Drupal\graphql_example\GraphQL\Field\MenuLink;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\graphql_example\GraphQL\Field\SelfAwareField;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Scalar\StringType;

class MenuLinkDescriptionField extends SelfAwareField {

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($value instanceof MenuLinkInterface) {
      return $value->getDescription();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'description';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new StringType();
  }
}