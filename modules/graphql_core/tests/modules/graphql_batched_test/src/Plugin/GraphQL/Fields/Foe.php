<?php

namespace Drupal\graphql_batched_test\Plugin\GraphQL\Fields;

/**
 * The users foe.
 *
 * @GraphQLField(
 *   id = "foe",
 *   secure = true,
 *   name = "foe",
 *   types = {"User"},
 *   type = "User",
 * )
 */
class Foe extends Users {

}
