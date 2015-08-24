<?php

/**
 * @file
 * Contains \Drupal\graphql\Field\DefaultFieldSchemaProvider.
 */

namespace Drupal\graphql\Field;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\EntitySchemaProviderInterface;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\Type;

class DefaultFieldSchemaProvider implements FieldSchemaProviderInterface {
  /**
   * {@inheritdoc}
   */
  public function getQuerySchema(EntitySchemaProviderInterface $entity_schema_provider, FieldDefinitionInterface $field_definition) {
    $type = Type::stringType();
    $type = $field_definition->isRequired() ? new NonNullModifier($type) : $type;
    $type = $field_definition->getFieldStorageDefinition()->isMultiple() ? new ListModifier($type) : $type;

    return [
      'name' => $field_definition->getName(),
      'description' => $field_definition->getDescription() ?: $field_definition->getLabel(),
      'type' => $type,
      'resolve' => [$this, 'resolveFieldValue'],
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
    return TRUE;
  }

  /**
   * @param $source
   * @param array $args
   * @param $root
   * @param Node $field
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|null
   */
  public function resolveFieldValue($source, array $args = null, $root, Node $field) {
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
    if ($first_item = $source->get($key)->first()) {
      return $first_item->getValue()[$main_property];
    }

    return NULL;
  }
}
