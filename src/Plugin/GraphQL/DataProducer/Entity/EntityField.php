<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\EntityFieldBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @DataProducer(
 *   id = "entity_field",
 *   name = @Translation("Entity field"),
 *   description = @Translation("Returns an entity field."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("The field value")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     ),
 *     "field_name" = @ContextDefinition("string",
 *       label = @Translation("Field name")
 *     ),
 *     "property" = @ContextDefinition("string",
 *       label = @Translation("Property name")
 *     )
 *   }
 * )
 */
class EntityField extends EntityFieldBase {

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return mixed
   */
  public function resolve(FieldableEntityInterface $entity, $field_name, $property = NULL, RefinableCacheableDependencyInterface $metadata, ResolveContext $context, ResolveInfo $info) {
    if ($entity->hasField($field_name)) {
      /** @var \Drupal\Core\Field\FieldItemListInterface $items */
      $items = $entity->get($field_name);
      $access = $items->access('view', NULL, TRUE);

      if ($access->isAllowed()) {
        return array_map(function($item) use ($property, $access, $metadata, $context, $info) {
          $output = !empty($property) ? $this->resolveItem($item, $property, $context, $info) : $item;
          $metadata->addCacheableDependency($access);
          return $output;
        }, iterator_to_array($items));
      }
    }
  }
}
