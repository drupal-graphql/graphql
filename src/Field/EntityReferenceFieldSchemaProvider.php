<?php

/**
 * @file
 * Contains \Drupal\graphql\Field\EntityReferenceFieldSchemaProvider.
 */

namespace Drupal\graphql\Field;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\EntitySchemaProviderInterface;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;

class EntityReferenceFieldSchemaProvider implements FieldSchemaProviderInterface {
  /**
   * Constructs a EntityReferenceFieldSchemaProvider object.
   *
   * @param EntityManagerInterface $entityManager
   *   The entity manager service.
   */
  public function __construct(EntityManagerInterface $entityManager) {
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuerySchema(EntitySchemaProviderInterface $entity_schema_provider, FieldDefinitionInterface $field_definition) {
    $target_entity_type_id = $field_definition->getSetting('target_type');
    $target_entity_type = $this->entityManager->getDefinition($target_entity_type_id);

    if (!$target_entity_type->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface')) {
      // We don't support config entities for now.
      return NULL;
    }

    $type = $this->getTypeResolver($entity_schema_provider, $target_entity_type_id);
    $type = $field_definition->isRequired() ? new NonNullModifier($type) : $type;
    $type = $field_definition->getFieldStorageDefinition()->isMultiple() ? new ListModifier($type) : $type;

    return [
      'name' => $field_definition->getName(),
      'description' => $field_definition->getDescription() ?: $field_definition->getLabel(),
      'type' => $type,
      'resolve' => $this->getEntityReferenceResolver($target_entity_type_id),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getMutationSchema(EntitySchemaProviderInterface $entity_schema_provider, FieldDefinitionInterface $field_definition) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function applies(FieldDefinitionInterface $field_definition) {
    return $field_definition->getType() === 'entity_reference';
  }

  /**
   * @param EntitySchemaProviderInterface $entity_schema_provider
   * @param $target_entity_type_id
   *
   * @return callable
   */
  protected function getTypeResolver(EntitySchemaProviderInterface $entity_schema_provider, $target_entity_type_id) {
    return function () use ($entity_schema_provider, $target_entity_type_id) {
      $target_entity_type = $this->entityManager->getDefinition($target_entity_type_id);

      return $target_entity_type->getBundleEntityType() !== 'bundle' ?
        $entity_schema_provider->getEntityTypeInterface($target_entity_type_id) :
        $entity_schema_provider->getEntityBundleType($target_entity_type_id, $target_entity_type_id);
    };
  }

  /**
   * @param $target_entity_type_id
   *
   * @return callable
   */
  protected function getEntityReferenceResolver($target_entity_type_id) {
    return function ($source, array $args = null, $root, Node $field) use ($target_entity_type_id) {
      if (!($source instanceof ContentEntityInterface)) {
        return null;
      }

      $key = $field->get('name')->get('value');
      $field_definition = $source->getFieldDefinition($key);
      $field_storage_definition = $field_definition->getFieldStorageDefinition();

      if ($field_storage_definition->isMultiple()) {
        return $source->get($key);
      }

      $main_property = $field_storage_definition->getMainPropertyName();
      $entity_id = $source->get($key)->first()->getValue()[$main_property];

      return $this->entityManager->getStorage($target_entity_type_id)->load($entity_id);
    };
  }
}
