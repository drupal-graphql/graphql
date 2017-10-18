<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Mutations;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the error code of an entity constraint violation.
 *
 * @GraphQLField(
 *   id = "constraint_violation_code",
 *   secure = true,
 *   name = "code",
 *   type = "String",
 *   parents = {"ConstraintViolation"},
 *   nullable = true
 * )
 */
class ConstraintViolationCode extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ConstraintViolationInterface) {
      yield $value->getCode();
    }
  }

}
