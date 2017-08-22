<?php

namespace Drupal\graphql_content_mutation\Plugin\GraphQL\Fields;

use Drupal\graphql_content_mutation\Plugin\GraphQL\DeleteEntityOutputWrapper;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the deleted entity object.
 *
 * @GraphQLField(
 *   id = "delete_entity_output_entity",
 *   secure = true,
 *   name = "entity",
 *   type = "Entity",
 *   types = {"DeleteEntityOutput"},
 *   nullable = true
 * )
 */
class DeleteEntityOutputEntity extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof DeleteEntityOutputWrapper) {
      if (($entity = $value->getEntity()) && $entity->access('view')) {
        yield $entity;
      }
    }
  }

}
