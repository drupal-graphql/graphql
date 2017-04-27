<?php

namespace Drupal\graphql\GraphQL\Type;

use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType as BaseAbstractInterfaceType;

abstract class AbstractInterfaceType extends BaseAbstractInterfaceType {
  /**
   * {@inheritdoc}
   */
  public function build($config) {
    // This method should not be required by any of our interface types.
  }
}