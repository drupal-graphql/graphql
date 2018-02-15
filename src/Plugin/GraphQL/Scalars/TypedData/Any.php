<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars\TypedData;

use Drupal\graphql\GraphQL\Type\Scalars\AnyType;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Scalars\GraphQLString;

/**
 * @GraphQLScalar(
 *   id = "any",
 *   name = "Any",
 *   type = "any"
 * )
 */
class Any extends GraphQLString {

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $this->definition = new AnyType();
    }

    return $this->definition;
  }
}
