<?php

namespace Drupal\graphql\GraphQL\Type\Scalars;

use Youshido\GraphQL\Type\Scalar\AbstractScalarType;

class MapType extends AbstractScalarType {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'Map';
  }

  /**
   * {@inheritdoc}
   */
  public function serialize($value) {
  }

  /**
   * {@inheritdoc}
   */
  public function isValidValue($value) {
    return is_array($value);
  }

}
