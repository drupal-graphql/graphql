<?php

namespace Drupal\graphql_content_mutation\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\graphql_content_mutation\Plugin\GraphQL\CreateEntityOutputWrapper;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a list of entity creation constraint violations.
 *
 * @GraphQLField(
 *   id = "create_entity_output_violations",
 *   name = "violations",
 *   type = "ConstraintViolation",
 *   types = {"CreateEntityOutput"},
 *   multi = true,
 *   nullable = false
 * )
 */
class CreateEntityOutputViolations extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof CreateEntityOutputWrapper) {
      if ($violations = $value->getViolations()) {
        foreach ($violations as $violation) {
          yield $violation;
        }
      }
    }
  }

}
