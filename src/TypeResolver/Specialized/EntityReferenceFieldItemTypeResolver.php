<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\Specialized\EntityReferenceFieldItemTypeResolver.
 */

namespace Drupal\graphql\TypeResolver\Specialized;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Field\TypedData\FieldItemDataDefinition;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Fubhy\GraphQL\Language\Node;

/**
 * Resolves typed data types.
 */
class EntityReferenceFieldItemTypeResolver extends FieldItemTypeResolver {
  /**
   * {@inheritdoc}
   */
  public function applies($type) {
    if ($type instanceof FieldItemDataDefinition) {
      return $type->getFieldDefinition()->getType() === 'entity_reference';
    }

    return FALSE;
  }

  /**
   * @param ComplexDataDefinitionInterface $definition
   *
   * @return array
   */
  protected function getFieldsFromProperties(ComplexDataDefinitionInterface $definition) {
    $fields = [];

    $property = $definition->getPropertyDefinition('target_id');
    if ($raw = $this->typeResolver->resolveRecursive($property)) {
      $fields['raw'] = [
        'type' => $raw,
        'resolve' => [__CLASS__, 'resolveRaw'],
      ];
    }

    $property = $definition->getPropertyDefinition('entity');
    $target_definition = $property->getTargetDefinition();
    $target_type_id = $target_definition->getEntityTypeId();
    $target_type = $this->entityManager->getDefinition($target_type_id);

    if (
      $target_type instanceof ContentEntityTypeInterface &&
      $target = $this->typeResolver->resolveRecursive($property)
    ) {
      $fields['target'] = [
        'type' => $target,
        'resolve' => [__CLASS__, 'resolveTarget'],
      ];
    }

    return $fields;
  }

  /**
   * @param mixed $source
   * @param array $args
   * @param mixed $root
   * @param \Fubhy\GraphQL\Language\Node $field
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface|null
   */
  public static function resolveRaw($source, array $args = NULL, $root, Node $field) {
    if (!($source instanceof TypedDataInterface)) {
      return NULL;
    }

    return $source->get('target_id')->getValue();
  }

  /**
   * @param mixed $source
   * @param array $args
   * @param mixed $root
   * @param \Fubhy\GraphQL\Language\Node $field
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface|null
   */
  public static function resolveTarget($source, array $args = NULL, $root, Node $field) {
    if (!($source instanceof TypedDataInterface)) {
      return NULL;
    }

    return $source->get('entity')->getTarget();
  }
}
