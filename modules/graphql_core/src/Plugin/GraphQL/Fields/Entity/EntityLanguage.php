<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * GraphQL field resolving an Entity's language.
 *
 * @GraphQLField(
 *   id = "entity_language",
 *   secure = true,
 *   name = "entityLanguage",
 *   type = "Language",
 *   parents = {"Entity"}
 * )
 */
class EntityLanguage extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof EntityInterface) {
      yield $value->language();
    }
  }

}
