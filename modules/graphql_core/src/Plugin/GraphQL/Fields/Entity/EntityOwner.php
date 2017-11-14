<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
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
  protected function buildType(PluggableSchemaBuilderInterface $schemaBuilder) {
    try {
      return $schemaBuilder->findByName('User', [GRAPHQL_INTERFACE_PLUGIN])->getDefinition($schemaBuilder);
    }
    catch (\Exception $exc) {
      return $schemaBuilder->findByName('Entity', [GRAPHQL_INTERFACE_PLUGIN])->getDefinition($schemaBuilder);
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
