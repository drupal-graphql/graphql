<?php

namespace Drupal\graphql_batched_test\Plugin\GraphQL\Fields;

/**
 * A list of friends.
 *
 * @GraphQLField(
 *   id = "friends",
 *   secure = true,
 *   name = "friends",
 *   parents = {"User"},
 *   type = "[User]"
 * )
 */
class Friends extends Users {

}
