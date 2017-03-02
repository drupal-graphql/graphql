<?php

namespace Drupal\graphql_example\GraphQL\Field\Page;

use Drupal\graphql_example\GraphQL\Field\SelfAwareField;
use Drupal\node\NodeInterface;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Scalar\IntType;

class PageNidField extends SelfAwareField {

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($value instanceof NodeInterface && $value->bundle() === 'page') {
      return (int) $value->id();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'nid';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new IntType();
  }
}