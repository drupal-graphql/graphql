<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Scalars;

use Youshido\GraphQL\Type\Scalar\FloatType;

/**
 * Scalar float type.
 *
 * @GraphQLScalar(
 *   id = "float",
 *   name = "Float",
 *   data_type = "float"
 * )
 */
class GraphQLFloat extends FloatType {

}
