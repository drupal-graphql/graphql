<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * GraphQL field resolving for entity access for the current user.
 *
 * @GraphQLField(
 *   id = "entity_access",
 *   secure = true,
 *   name = "entityAccess",
 *   type = "Boolean",
 *   parents = {"Entity"},
 *   arguments = {
 *     "operation" = "String"
 *   }
 * )
 */
class EntityAccess extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof EntityInterface) {
      yield $value->access($args['operation']);
    }
  }

}
