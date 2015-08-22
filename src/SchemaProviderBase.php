<?php

namespace Drupal\graphql;

use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;

/**
 * Abstract base class for schema providers.
 */
abstract class SchemaProviderBase implements SchemaProviderInterface {
  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return new Schema(
        new ObjectType('Query', $this->getQuerySchema()),
        new ObjectType('Mutation', $this->getMutationSchema())
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuerySchema() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getMutationSchema() {
    return [];
  }
}
