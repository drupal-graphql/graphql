<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Scalars;

use Youshido\GraphQL\Type\Scalar\IntType;

/**
 * Scalar integer type.
 *
 * @GraphQLScalar(
 *   id = "int",
 *   name = "Int",
 *   data_type = "integer"
 * )
 */
class GraphQLInteger extends IntType {

}
