<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Mutations;

use Drupal\graphql_core\GraphQL\EntityCrudOutputWrapper;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a list of error messages.
 *
 * @GraphQLField(
 *   id = "entity_crud_output_errors",
 *   secure = true,
 *   name = "errors",
 *   type = "String",
 *   parents = {"EntityCrudOutput"},
 *   multi = true,
 *   nullable = false
 * )
 */
class EntityCrudOutputErrors extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof EntityCrudOutputWrapper) {
      if ($errors = $value->getErrors()) {
        foreach ($errors as $error) {
          yield $error;
        }
      }
    }
  }

}
