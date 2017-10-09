<?php

namespace Drupal\graphql_mutation\Plugin\GraphQL\Types;

use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;

/**
 * GraphQL type for validation constraint violations.
 *
 * @GraphQLType(
 *   id = "constraint_violation",
 *   name = "ConstraintViolation"
 * )
 */
class ConstraintViolation extends TypePluginBase {

}
