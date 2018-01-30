<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\user\EntityOwnerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Get an entities owner if it implements the EntityOwnerInterface.
 *
 * @GraphQLField(
 *   id = "entity_owner",
 *   secure = true,
 *   name = "entityOwner",
 *   type = "entity:user",
 *   parents = {"Entity"}
 * )
 */
class EntityOwner extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof EntityOwnerInterface) {
      yield $value->getOwner();
    }
  }

}
