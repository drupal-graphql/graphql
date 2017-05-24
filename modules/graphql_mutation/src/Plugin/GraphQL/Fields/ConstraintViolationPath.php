<?php

namespace Drupal\graphql_mutation\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the path of an entity constraint violation.
 *
 * @GraphQLField(
 *   id = "constraint_violation_path",
 *   name = "path",
 *   type = "String",
 *   types = {"ConstraintViolation"},
 *   nullable = true
 * )
 */
class ConstraintViolationPath extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ConstraintViolationInterface){
      yield $value->getPropertyPath();
    }
  }

}
