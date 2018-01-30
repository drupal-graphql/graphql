<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars\TypedData;

use Drupal\graphql\Plugin\GraphQL\Scalars\GraphQLString;

/**
 * @GraphQLScalar(
 *   id = "email",
 *   name = "Email",
 *   type = "email"
 * )
 */
class Email extends GraphQLString {

}
