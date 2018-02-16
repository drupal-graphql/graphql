<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use GraphQL\Type\Definition\Type;

/**
 * Scalar id type.
 *
 * @GraphQLScalar(
 *   id = "id",
 *   name = "ID"
 * )
 */
class GraphQLId extends ScalarPluginBase {

}
