<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface;
use Drupal\user\EntityOwnerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Get an entities owner if it implements the EntityOwnerInterface.
 *
 * @GraphQLField(
 *   id = "entity_owner",
 *   secure = true,
 *   name = "entityOwner",
 *   parents = {"Entity"}
 * )
 */
class EntityOwner extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function buildType(SchemaBuilderInterface $schemaManager) {
    try {
      return $schemaManager->findByName('User', [GRAPHQL_INTERFACE_PLUGIN]);
    }
    catch (\Exception $exc) {
      return $schemaManager->findByName('Entity', [GRAPHQL_INTERFACE_PLUGIN]);
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
