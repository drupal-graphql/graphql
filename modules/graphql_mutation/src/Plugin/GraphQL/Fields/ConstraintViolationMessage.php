<?php

namespace Drupal\graphql_mutation\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the message of an entity constraint violation.
 *
 * @GraphQLField(
 *   id = "constraint_violation_message",
 *   name = "message",
 *   type = "String",
 *   types = {"ConstraintViolation"},
 *   nullable = true
 * )
 */
class ConstraintViolationMessage extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ConstraintViolationInterface){
      yield $value->getMessage();
    }
  }

}
