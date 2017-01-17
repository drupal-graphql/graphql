<?php

namespace Drupal\graphql_example\GraphQL\Relay\Field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql_example\RouteObjectWrapper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Relay\Field\GlobalIdField as GlobalIdFieldBase;
use Youshido\GraphQL\Relay\Node;

class GlobalIdField extends GlobalIdFieldBase implements ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($value instanceof EntityInterface) {
      return Node::toGlobalId($this->typeName, $value->id());
    }

    return NULL;
  }
}