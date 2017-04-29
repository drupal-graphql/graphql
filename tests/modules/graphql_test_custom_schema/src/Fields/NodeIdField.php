<?php

namespace Drupal\graphql_test_custom_schema\Fields;

use Drupal\node\NodeInterface;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\IntType;

class NodeIdField extends AbstractField {

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new NonNullType(new IntType());
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($value instanceof NodeInterface) {
      return (int) $value->id();
    }

    return NULL;
  }
}