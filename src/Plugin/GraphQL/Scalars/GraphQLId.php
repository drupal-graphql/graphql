<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Youshido\GraphQL\Type\Scalar\IdType;

/**
 * Scalar id type.
 *
 * @GraphQLScalar(
 *   id = "id",
 *   name = "ID",
 *   data_type = "id"
 * )
 */
class GraphQLId extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $this->definition = new IdType();
    }

    return $this->definition;
  }
}
