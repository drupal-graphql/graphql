<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Youshido\GraphQL\Type\Scalar\DateTimeType;

/**
 * Scalar string type.
 *
 * @GraphQLScalar(
 *   id = "date_time",
 *   name = "DateTime"
 * )
 */
class GraphQLDateTime extends DateTimeType {

}
