<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use GraphQL\Type\Definition\Type;

/**
 * Scalar integer type.
 *
 * @GraphQLScalar(
 *   id = "int",
 *   name = "Int",
 *   type = "integer"
 * )
 */
class GraphQLInteger extends ScalarPluginBase {

}

