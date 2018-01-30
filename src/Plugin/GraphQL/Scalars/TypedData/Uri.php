<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars\TypedData;

use Drupal\graphql\Plugin\GraphQL\Scalars\GraphQLString;

/**
 * @GraphQLScalar(
 *   id = "uri",
 *   name = "Uri",
 *   type = "uri"
 * )
 */
class Uri extends GraphQLString {

}
