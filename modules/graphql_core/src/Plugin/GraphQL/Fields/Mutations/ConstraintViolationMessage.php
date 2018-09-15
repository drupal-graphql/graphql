<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Mutations;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Retrieve the message of an entity constraint violation.
 *
 * @GraphQLField(
 *   id = "constraint_violation_message",
 *   secure = true,
 *   name = "message",
 *   type = "String",
 *   parents = {"ConstraintViolation"}
 * )
 */
class ConstraintViolationMessage extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof ConstraintViolationInterface){
      yield $value->getMessage();
    }
  }

}
