<?php

namespace Drupal\graphql;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\node\NodeInterface;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\InterfaceType;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Generates a GraphQL Schema for content entity types.
 */
class EntitySchemaProvider extends SchemaProviderBase {
  /**
   * Constructs a EntitySchemaProvider object.
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
  public function getQuerySchema() {
    $schema = [];

    // We only support content entity types.
    /** @var EntityTypeInterface[] $entity_types */
    $entity_types = array_filter($this->entityManager->getDefinitions(), function (EntityTypeInterface $entity_type) {
      return $entity_type->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface');
    });

    foreach ($entity_types as $entity_type_id => $entity_type) {
      $base_fields = [];

      foreach ($this->entityManager->getBaseFieldDefinitions($entity_type_id) as $field_name => $field_definition) {
        /** @var \Drupal\Core\Field\FieldDefinitionInterface $field_definition */

        $type = Type::stringType();
        $type = $field_definition->isRequired() ? new NonNullModifier($type) : $type;
        $type = $field_definition->getFieldStorageDefinition()->isMultiple() ? new ListModifier($type) : $type;

        $base_fields[$field_name] = [
          'name' => $field_name,
          'description' => $field_definition->getDescription() ?: $field_definition->getLabel(),
          'type' => $type,
          'resolve' => [$this, 'resolveFieldValue'],
        ];
      }

      $entity_type_interface = new InterfaceType($entity_type_id, $base_fields, null, $entity_type->getLabel());

      foreach ($this->entityManager->getBundleInfo($entity_type_id) as $bundle_name => $bundle)  {
        $bundle_fields = [];

        foreach ($this->entityManager->getFieldDefinitions($entity_type_id, $bundle_name) as $field_name => $field_definition) {
          $field_storage_definition = $field_definition->getFieldStorageDefinition();

          if ($field_storage_definition->isBaseField()) {
            continue;
          }

          $type = Type::stringType();
          $type = $field_definition->isRequired() ? new NonNullModifier($type) : $type;
          $type = $field_storage_definition->isMultiple() ? new ListModifier($type) : $type;

          $bundle_fields[$field_name] = [
            'name' => $field_name,
            'description' => $field_definition->getDescription() ?: $field_definition->getLabel(),
            'type' => $type,
            'resolve' => [$this, 'resolveFieldValue'],
          ];
        }

        $schema["$entity_type_id:$bundle_name"] = [
          'type' => new ObjectType($entity_type_id, $base_fields + $bundle_fields, [$entity_type_interface], function ($value) use ($bundle_name) {
            if ($value instanceof EntityInterface) {
              return $value->bundle() === $bundle_name;
            }
          }),
        ];
      }

      $schema[$entity_type_id] = [
        'type' => $entity_type_interface,
        'args' => [
          'id' => [
            'type' => Type::idType(),
          ],
        ],
        'resolve' => [$this, 'resolveEntity'],
      ];
    }

    return $schema;
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
    if (!($source instanceof NodeInterface)) {
      return null;
    }

    $key = $field->get('name')->get('value');
    $field_definition = $source->getFieldDefinition($key)->getFieldStorageDefinition();

    if ($field_definition->isMultiple()) {
      return $source->get($key);
    }

    $main_property = $field_definition->getMainPropertyName();
    return $source->get($key)->first()->getValue()[$main_property];
  }

  /**
   * @param $source
   * @param array $args
   * @param $root
   * @param Node $field
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public function resolveEntity($source, array $args = null, $root, Node $field) {
    $key = $field->get('name')->get('value');
    return $this->entityManager->getStorage($key)->load($args['id']);
  }
}
