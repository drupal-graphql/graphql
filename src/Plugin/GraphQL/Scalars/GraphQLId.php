<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Youshido\GraphQL\Type\Scalar\IdType;

/**
 * Scalar id type.
 *
 * @GraphQLScalar(
 *   id = "id",
 *   name = "ID",
 *   data_type = "id"
 * )
 */
class GraphQLId extends IdType {

}
