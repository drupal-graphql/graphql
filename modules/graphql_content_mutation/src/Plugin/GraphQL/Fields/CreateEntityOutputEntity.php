<?php

namespace Drupal\graphql_content_mutation\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\graphql_content_mutation\Plugin\GraphQL\CreateEntityOutputWrapper;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a the created entity.
 *
 * @GraphQLField(
 *   id = "create_entity_output_entity",
 *   secure = true,
 *   name = "entity",
 *   type = "Entity",
 *   types = {"CreateEntityOutput"},
 *   nullable = true
 * )
 */
class CreateEntityOutputEntity extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof CreateEntityOutputWrapper) {
      if (($entity = $value->getEntity()) && $entity->access('view')) {
        yield $entity;
      }
    }
  }

}
