<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Youshido\GraphQL\Type\Scalar\IntType;

/**
 * Scalar integer type.
 *
 * @GraphQLScalar(
 *   id = "int",
 *   name = "Int",
 *   data_type = "integer"
 * )
 */
class GraphQLInteger extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $this->definition = new IntType();
    }

    return $this->definition;
  }
}

