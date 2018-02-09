<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Youshido\GraphQL\Type\Scalar\DateTimeType;

/**
 * Scalar string type.
 *
 * @GraphQLScalar(
 *   id = "date_time",
 *   name = "DateTime"
 * )
 */
class GraphQLDateTime extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $this->definition = new DateTimeType();
    }

    return $this->definition;
  }
}
