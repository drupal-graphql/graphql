<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Youshido\GraphQL\Type\Scalar\FloatType;

/**
 * Scalar float type.
 *
 * @GraphQLScalar(
 *   id = "float",
 *   name = "Float",
 *   data_type = "float"
 * )
 */
class GraphQLFloat extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $this->definition = new FloatType();
    }

    return $this->definition;
  }
}

