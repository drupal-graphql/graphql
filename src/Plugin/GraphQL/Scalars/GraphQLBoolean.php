<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use GraphQL\Type\Definition\Type;

/**
 * Scalar boolean type.
 *
 * @GraphQLScalar(
 *   id = "boolean",
 *   name = "Boolean",
 *   type = "boolean"
 * )
 */
class GraphQLBoolean extends ScalarPluginBase {

}
