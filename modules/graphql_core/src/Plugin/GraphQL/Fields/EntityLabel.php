<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * GraphQL field resolving an Entity's id.
 *
 * @GraphQLField(
 *   id = "entity_label",
 *   secure = true,
 *   name = "entityLabel",
 *   type = "String",
 *   types = {"Entity"}
 * )
 */
class EntityLabel extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof EntityInterface) {
      yield $value->label();
    }
  }

}
