<?php

namespace Drupal\graphql_mutation\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the error code of an entity constraint violation.
 *
 * @GraphQLField(
 *   id = "constraint_violation_code",
 *   name = "code",
 *   type = "String",
 *   types = {"ConstraintViolation"},
 *   nullable = true
 * )
 */
class ConstraintViolationCode extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ConstraintViolationInterface){
      yield $value->getCode();
    }
  }

}
