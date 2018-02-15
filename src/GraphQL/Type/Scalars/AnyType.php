<?php

namespace Drupal\graphql\GraphQL\Type\Scalars;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Youshido\GraphQL\Type\Scalar\AbstractScalarType;

class AnyType extends AbstractScalarType {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'Any';
  }

  /**
   * {@inheritdoc}
   */
  public function serialize($value) {
    if (is_scalar($value)) {
      return $value;
    }

    if (is_array($value)) {
      return json_encode($value);
    }

    if (is_object($value) && method_exists($value, '__toString')) {
      return (string) $value;
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
