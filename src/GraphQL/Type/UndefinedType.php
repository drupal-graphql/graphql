<?php

namespace Drupal\graphql\GraphQL\Type;

use Youshido\GraphQL\Type\Scalar\AbstractScalarType;

class UndefinedType extends AbstractScalarType {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'Undefined';
  }

  /**
   * {@inheritdoc}
   */
  public function serialize($value) {
    if (is_bool($value) || is_null($value) || is_scalar($value)) {
      return $value;
    }

    if (is_object($value) && method_exists($value, '__toString')) {
      return (string) $value;
    }

    if (is_array($value) || is_object($value)) {
      return json_encode($value);
    }

    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function isValidValue($value) {
    return TRUE;
  }

}
