<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Youshido\GraphQL\Type\Scalar\StringType;

/**
 * Scalar string type.
 *
 * @GraphQLScalar(
 *   id = "string",
 *   name = "String",
 *   data_type = "string"
 * )
 */
class GraphQLString extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $this->definition = new StringType();
    }

    return $this->definition;
  }
}
