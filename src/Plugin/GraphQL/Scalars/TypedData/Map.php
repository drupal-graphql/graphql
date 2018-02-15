<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars\TypedData;

use Drupal\graphql\GraphQL\Type\Scalars\MapType;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\Scalars\GraphQLString;
use Drupal\graphql\Plugin\GraphQL\Scalars\ScalarPluginBase;

/**
 * @GraphQLScalar(
 *   id = "map",
 *   name = "Map",
 *   type = "map"
 * )
 */
class Map extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $this->definition = new MapType();
    }

    return $this->definition;
  }

}
