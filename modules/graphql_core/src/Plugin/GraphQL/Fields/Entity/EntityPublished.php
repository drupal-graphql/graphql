<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Get an published state.
 *
 * @GraphQLField(
 *   id = "entity_published",
 *   secure = true,
 *   name = "entityPublished",
 *   type = "Boolean",
 *   parents = {"Entity"}
 * )
 */
class EntityPublished extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof EntityPublishedInterface) {
      yield $value->isPublished();
    }
  }

}
