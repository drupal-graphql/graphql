<?php

namespace Drupal\graphql_example\GraphQL\Field\Menu;

use Drupal\graphql_example\GraphQL\Field\SelfAwareField;
use Drupal\system\MenuInterface;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Scalar\StringType;

class MenuDescriptionField extends SelfAwareField {

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($value instanceof MenuInterface) {
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