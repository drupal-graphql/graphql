<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Mutations;

use Drupal\graphql_core\GraphQL\EntityCrudOutputWrapper;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a list of entity mutation constraint violations.
 *
 * @GraphQLField(
 *   id = "entity_crud_output_violations",
 *   secure = true,
 *   name = "violations",
 *   type = "ConstraintViolation",
 *   parents = {"EntityCrudOutput"},
 *   multi = true,
 *   nullable = false
 * )
 */
class EntityCrudOutputViolations extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof EntityCrudOutputWrapper) {
      if ($violations = $value->getViolations()) {
        foreach ($violations as $violation) {
          yield $violation;
        }
      }
    }
  }

}
