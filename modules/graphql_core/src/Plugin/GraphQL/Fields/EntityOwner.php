<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Drupal\user\EntityOwnerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Get an entities owner if it implements the EntityOwnerInterface.
 *
 * @GraphQLField(
 *   id = "entity_owner",
 *   secure = true,
 *   name = "entityOwner",
 *   types = {"Entity"}
 * )
 */
class EntityOwner extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function buildType(GraphQLSchemaManagerInterface $schemaManager) {
    try {
      return $schemaManager->findByName('User', [GRAPHQL_CORE_INTERFACE_PLUGIN]);
    }
    catch (\Exception $exc) {
      return $schemaManager->findByName('Entity', [GRAPHQL_CORE_INTERFACE_PLUGIN]);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof EntityOwnerInterface) {
      yield $value->getOwner();
    }
  }

}
