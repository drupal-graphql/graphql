<?php

namespace Drupal\graphql_example\GraphQL\Field\Page;

use Drupal\graphql_example\GraphQL\Field\SelfAwareField;
use Drupal\node\NodeInterface;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Scalar\StringType;

class PageTitleField extends SelfAwareField {

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($value instanceof NodeInterface && $value->bundle() === 'page') {
      return $value->label();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'title';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new StringType();
  }
}