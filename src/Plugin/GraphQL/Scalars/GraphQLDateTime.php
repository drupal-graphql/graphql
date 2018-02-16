<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use GraphQL\Type\Definition\Type;

/**
 * Scalar string type.
 *
 * @GraphQLScalar(
 *   id = "date_time",
 *   name = "DateTime"
 * )
 */
class GraphQLDateTime extends ScalarPluginBase {

}
