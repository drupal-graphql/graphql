<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars\Upload;

use Drupal\graphql\Plugin\GraphQL\Scalars\ScalarPluginBase;

/**
 * @GraphQLScalar(
 *   id = "upload",
 *   name = "Upload"
 * )
 */
class Upload extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function serialize($value) {
    throw new \LogicException('Cannot serialize uploaded files.');
  }

  /**
   * {@inheritdoc}
   */
  public static function parseValue($value) {
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public static function parseLiteral($ast) {
    throw new \LogicException('Uploaded files have to be referenced in variables.');
  }

}
