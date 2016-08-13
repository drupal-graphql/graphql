<?php

namespace Drupal\graphql\GraphQL\Relay\Field;

use Drupal\Core\Entity\EntityInterface;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Relay\Field\GlobalIdField as GlobalIdFieldBase;
use Youshido\GraphQL\Relay\Node;

class GlobalIdField extends GlobalIdFieldBase {

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($value && $value instanceof EntityInterface) {
      return Node::toGlobalId($this->typeName, $value->id());
    }

    return parent::resolve($value, $args, $info);
  }
}