<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Youshido\GraphQL\Type\Scalar\BooleanType;

/**
 * Scalar boolean type.
 *
 * @GraphQLScalar(
 *   id = "boolean",
 *   name = "Boolean",
 *   data_type = "boolean"
 * )
 */
class GraphQLBoolean extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $this->definition = new BooleanType();
    }

    return $this->definition;
  }
}
