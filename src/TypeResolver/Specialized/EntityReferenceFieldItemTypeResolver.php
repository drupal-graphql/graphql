<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\Specialized\EntityReferenceFieldItemTypeResolver.
 */

namespace Drupal\graphql\TypeResolver\Specialized;

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
    $this->typeResolver->resolveRecursive($definition->getPropertyDefinition('entity'));

    return [
      'value' => [
        'type' => $this->typeResolver->resolveRecursive($definition->getPropertyDefinition('target_id')),
        'resolve' => [__CLASS__, 'resolveRaw'],
      ],
      'entity' => [
        'type' => $this->typeResolver->resolveRecursive($definition->getPropertyDefinition('entity')),
        'resolve' => [__CLASS__, 'resolveTarget'],
      ],
    ];
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
