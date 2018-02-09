<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars\TypedData;

use Drupal\graphql\Plugin\GraphQL\Scalars\GraphQLString;

/**
 * @GraphQLScalar(
 *   id = "date_time_iso8601",
 *   name = "DateTimeIso8601",
 *   type = "date_time_iso8601"
 * )
 */
class DateTimeIso8601 extends GraphQLString {

}
