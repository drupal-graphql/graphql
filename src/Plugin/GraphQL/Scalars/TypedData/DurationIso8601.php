<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars\TypedData;

use Drupal\graphql\Plugin\GraphQL\Scalars\GraphQLString;

/**
 * @GraphQLScalar(
 *   id = "duration_iso8601",
 *   name = "DurationIso8601",
 *   type = "duration_iso8601"
 * )
 */
class DurationIso8601 extends GraphQLString {

}
