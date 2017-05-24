<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Fields;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * GraphQL field resolving an Entity's language.
 *
 * @GraphQLField(
 *   id = "entity_language",
 *   name = "entityLanguage",
 *   type = "Language",
 *   types = {"Entity"}
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
