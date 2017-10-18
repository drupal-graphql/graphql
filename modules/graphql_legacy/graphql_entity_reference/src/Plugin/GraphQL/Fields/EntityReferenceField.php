<?php

namespace Drupal\graphql_entity_reference\Plugin\GraphQL\Fields;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Expose entity reference fields as objects.
 *
 * @GraphQLField(
 *   id = "entity_reference",
 *   secure = true,
 *   field_formatter = "graphql_entity_reference",
 *   schema_cache_tags = {"entity_field_info"},
 *   deriver = "Drupal\graphql_entity_reference\Plugin\Deriver\EntityReferenceFields"
 * )
 */
class EntityReferenceField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ContentEntityInterface) {
      foreach ($value->get($this->getPluginDefinition()['field']) as $item) {
        if ($item instanceof EntityReferenceItem && $item->entity && $item->entity->access('view')) {
          yield $item->entity;
        }
      }
    }
  }

}
