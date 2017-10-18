<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Mutations;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the path of an entity constraint violation.
 *
 * @GraphQLField(
 *   id = "constraint_violation_path",
 *   secure = true,
 *   name = "path",
 *   type = "String",
 *   parents = {"ConstraintViolation"},
 *   nullable = true
 * )
 */
class ConstraintViolationPath extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ConstraintViolationInterface) {
      yield $value->getPropertyPath();
    }
  }

}
