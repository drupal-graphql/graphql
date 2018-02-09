<?php

namespace Drupal\graphql\GraphQL\Type;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Youshido\GraphQL\Type\Scalar\AbstractScalarType;

class UploadType extends AbstractScalarType {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'Upload';
  }

  /**
   * {@inheritdoc}
   */
  public function serialize($value) {
    throw new \LogicException('Cannot serialize uploaded files.');
  }

  /**
   * {@inheritdoc}
   */
  public function parseValue($value) {
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function parseInputValue($value) {
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function isValidValue($value) {
    return $value instanceof UploadedFile;
  }

}
