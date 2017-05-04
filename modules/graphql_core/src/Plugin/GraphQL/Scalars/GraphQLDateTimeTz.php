<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Scalars;

use Youshido\GraphQL\Type\Scalar\DateTimeTzType;

/**
 * Scalar string type.
 *
 * @GraphQLScalar(
 *   id = "date_time_tz",
 *   name = "DateTimeTz"
 * )
 */
class GraphQLDateTimeTz extends DateTimeTzType {

}
