<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

/**
 * @GraphQLScalar(
 *   id = "upload",
 *   name = "Upload"
 * )
 */
class GraphQLUpload extends ScalarPluginBase {

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
