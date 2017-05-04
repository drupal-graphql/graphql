<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Fields;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * GraphQL field resolving an entity's bundle.
 *
 * @GraphQLField(
 *   id = "entity_bundle",
 *   name = "entityBundle",
 *   type = "String",
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
