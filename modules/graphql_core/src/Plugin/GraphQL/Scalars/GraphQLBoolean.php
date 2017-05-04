<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Scalars;

use Youshido\GraphQL\Type\Scalar\BooleanType;

/**
 * Scalar boolean type.
 *
 * @GraphQLScalar(
 *   id = "boolean",
 *   name = "Boolean",
 *   data_type = "boolean"
 * )
 */
class GraphQLBoolean extends BooleanType {

}
