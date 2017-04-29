<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Scalars;

use Youshido\GraphQL\Type\Scalar\BooleanType;

/**
 * Scalar boolean type.
 *
 * @GraphQLScalar(
 *   name="Boolean",
 *   dataType="boolean"
 * )
 */
class GraphQLBoolean extends BooleanType {

}
