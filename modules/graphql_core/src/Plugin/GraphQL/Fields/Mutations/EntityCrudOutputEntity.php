<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Mutations;

use Drupal\graphql_core\GraphQL\EntityCrudOutputWrapper;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a the mutated entity.
 *
 * @GraphQLField(
 *   id = "entity_crud_output_entity",
 *   secure = true,
 *   name = "entity",
 *   type = "Entity",
 *   parents = {"EntityCrudOutput"},
 *   nullable = true
 * )
 */
class EntityCrudOutputEntity extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof EntityCrudOutputWrapper) {
      if (($entity = $value->getEntity()) && $entity->access('view')) {
        yield $entity;
      }
    }
  }

}
