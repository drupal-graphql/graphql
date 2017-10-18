<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * GraphQL field resolving an entity's bundle.
 *
 * @GraphQLField(
 *   id = "entity_bundle",
 *   secure = true,
 *   name = "entityBundle",
 *   type = "String",
 *   parents = {"Entity"}
 * )
 */
class EntityBundle extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof EntityInterface) {
      yield $value->bundle();
    }
  }
}
