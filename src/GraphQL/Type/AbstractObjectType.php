<?php

namespace Drupal\graphql\GraphQL\Type;

use Youshido\GraphQL\Type\Object\AbstractObjectType as BaseAbstractObjectType;

abstract class AbstractObjectType extends BaseAbstractObjectType {
  /**
   * {@inheritdoc}
   */
  public function build($config) {
    // This method should not be required by any of our object types.
  }
}