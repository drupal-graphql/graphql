<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars\TypedData;

use Drupal\graphql\GraphQL\Type\UndefinedType;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Scalars\ScalarPluginBase;

/**
 * @GraphQLScalar(
 *   id = "undefined",
 *   name = "Undefined",
 *   type = "undefined"
 * )
 */
class Undefined extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $this->definition = new UndefinedType();
    }

    return $this->definition;
  }

}
