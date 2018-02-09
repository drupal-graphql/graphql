<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars\TypedData;

use Drupal\graphql\Plugin\GraphQL\Scalars\GraphQLInteger;

/**
 * @GraphQLScalar(
 *   id = "timestamp",
 *   name = "Timestamp",
 *   type = "timestamp"
 * )
 */
class Timestamp extends GraphQLInteger {

}
