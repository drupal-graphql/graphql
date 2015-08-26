<?php

/**
 * @file
 * Contains \Drupal\graphql\EntitySchemaProvider.
 */

namespace Drupal\graphql;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\Field\FieldSchemaProviderInterface;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\InterfaceType;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Generates a GraphQL Schema for content entity types.
 */
class EntitySchemaProvider extends SchemaProviderBase implements EntitySchemaProviderInterface {
  protected $baseFieldsSchema = [];
  protected $bundleFieldsSchema = [];
  protected $entityTypeInterfaces = [];
  protected $entityBundleTypes = [];

  /**
   * Constructs a EntitySchemaProvider object.
   *
   * @param EntityManagerInterface $entityManager
   *   The entity manager service.
   * @param FieldSchemaProviderInterface $fieldSchemaProvider
   *   The field schema provider service.
   */
  public function __construct(EntityManagerInterface $entityManager, FieldSchemaProviderInterface $fieldSchemaProvider) {
    $this->entityManager = $entityManager;
    $this->fieldSchemaProvider = $fieldSchemaProvider;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuerySchema() {
    // We only support content entity types.
    $entity_types = array_filter($this->entityManager->getDefinitions(), function (EntityTypeInterface $entity_type) {
      return $entity_type->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface');
    });

    $schema = array_reduce($entity_types, function (array $carry, EntityTypeInterface $entity_type) {
      $entity_type_id = $entity_type->id();
      $bundle_entity_type_id = $entity_type->getBundleEntityType();

      if ($bundle_entity_type_id !== 'bundle' && $entity_type_schema = $this->getEntityTypeSchema($entity_type_id)) {
        $carry[$this->underscoreToCamelCase($entity_type_id)] = $entity_type_schema;
      }

      $bundle_names = array_keys($this->entityManager->getBundleInfo($entity_type_id));
      return array_reduce($bundle_names, function (array $carry, $bundle_name) use ($entity_type_id, $bundle_entity_type_id) {
        if ($entity_bundle_schema = $this->getEntityBundleSchema($entity_type_id, $bundle_name)) {
          $key = $bundle_entity_type_id !== 'bundle' ?
            $this->underscoreToCamelCase("{$entity_type_id}_{$bundle_name}") :
            $this->underscoreToCamelCase($entity_type_id);

          $carry[$key] = $entity_bundle_schema;
        }

        return $carry;
      }, $carry);
    }, []);

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeInterface($entity_type_id) {
    if (!array_key_exists($entity_type_id, $this->entityTypeInterfaces)) {
      $entity_type = $this->entityManager->getDefinition($entity_type_id);
      $base_fields = $this->getBaseFieldsSchema($entity_type_id);
      $type_name = $this->underscoreToCamelCase($entity_type_id);

      $this->entityTypeInterfaces[$entity_type_id] = new InterfaceType($type_name, $base_fields, function ($entity) {
        if ($entity instanceof ContentEntityInterface) {
          return $this->getEntityBundleType($entity->getEntityTypeId(), $entity->bundle());
        }
      }, $entity_type->getLabel());
    }

    return $this->entityTypeInterfaces[$entity_type_id];
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityBundleType($entity_type_id, $bundle_name) {
    if (!array_key_exists("$entity_type_id:$bundle_name", $this->entityBundleTypes)) {
      $entity_type_interface = $this->getEntityTypeInterface($entity_type_id);
      $fields = $this->getFieldsSchema($entity_type_id, $bundle_name);

      $entity_type = $this->entityManager->getDefinition($entity_type_id);
      $bundle_entity_type_id = $entity_type->getBundleEntityType();
      $interfaces = $bundle_entity_type_id !== 'bundle' ? [$entity_type_interface] : [];
      $type_name = $bundle_entity_type_id !== 'bundle' ?
        $this->underscoreToCamelCase("${entity_type_id}_${bundle_name}") :
        $this->underscoreToCamelCase($entity_type_id);

      $this->entityBundleTypes["$entity_type_id:$bundle_name"] = new ObjectType($type_name, $fields, $interfaces);
    }

    return $this->entityBundleTypes["$entity_type_id:$bundle_name"];
  }

  /**
   * @param string $entity_type_id
   *
   * @return array
   */
  protected function getEntityTypeSchema($entity_type_id) {
    $entity_type = $this->entityManager->getDefinition($entity_type_id);

    return [
      'type' => $this->getEntityTypeInterface($entity_type_id),
      'description' => $entity_type->getLabel(),
      'args' => [
        'id' => [
          'type' => Type::idType(),
        ],
      ],
      'resolve' => $this->getEntityResolver($entity_type_id),
    ];
  }

  /**
   * @param string $entity_type_id
   * @param string $bundle_name
   *
   * @return array
   */
  protected function getEntityBundleSchema($entity_type_id, $bundle_name) {
    $entity_type = $this->entityManager->getDefinition($entity_type_id);
    $bundle_info = $this->entityManager->getBundleInfo($entity_type_id);

    return [
      'type' => $this->getEntityBundleType($entity_type_id, $bundle_name),
      'description' => $entity_type->getLabel() . ': ' . $bundle_info[$bundle_name]['label'],
      'args' => [
        'id' => [
          'type' => Type::idType(),
        ],
      ],
      'resolve' => $this->getEntityResolver($entity_type_id),
    ];
  }

  /**
   * @param $entity_type_id
   *
   * @return callable
   */
  protected function getEntityResolver($entity_type_id) {
    return function ($source, array $args = null, $root, Node $field) use ($entity_type_id) {
      return $this->entityManager->getStorage($entity_type_id)->load($args['id']);
    };
  }

  /**
   * @param string $entity_type_id
   * @param string $bundle_name
   *
   * @return array
   */
  protected function getBundleFieldsSchema($entity_type_id, $bundle_name) {
    if (!array_key_exists("$entity_type_id:$bundle_name", $this->bundleFieldsSchema)) {
      $field_definitions = $this->entityManager->getFieldDefinitions($entity_type_id, $bundle_name);

      $bundle_fields = array_filter($field_definitions, function (FieldDefinitionInterface $field_definition) {
        return !$field_definition->getFieldStorageDefinition()->isBaseField();
      });

      $this->bundleFieldsSchema["$entity_type_id:$bundle_name"] = array_filter(array_map(function (FieldDefinitionInterface $field_definition) {
        return $this->fieldSchemaProvider->getQuerySchema($this, $field_definition);
      }, $bundle_fields));
    }

    return $this->bundleFieldsSchema["$entity_type_id:$bundle_name"];
  }

  /**
   * @param string $entity_type_id
   *
   * @return array
   */
  protected function getBaseFieldsSchema($entity_type_id) {
    if (!array_key_exists($entity_type_id, $this->baseFieldsSchema)) {
      $this->baseFieldsSchema[$entity_type_id] = array_filter(array_map(function (FieldDefinitionInterface $field_definition) {
        return $this->fieldSchemaProvider->getQuerySchema($this, $field_definition);
      }, $this->entityManager->getBaseFieldDefinitions($entity_type_id)));
    }

    return $this->baseFieldsSchema[$entity_type_id];
  }

  /**
   * @param $entity_type_id
   * @param $bundle_name
   *
   * @return array
   */
  protected function getFieldsSchema($entity_type_id, $bundle_name) {
    $base_fields = $this->getBaseFieldsSchema($entity_type_id);
    $bundle_fields = $this->getBundleFieldsSchema($entity_type_id, $bundle_name);
    return array_merge($base_fields, $bundle_fields);
  }

  /**
   * @param string $string
   *
   * @return string
   */
  protected function underscoreToCamelCase($string) {
    $words = explode('_', strtolower($string));
    return lcfirst(implode('', array_map('ucfirst', array_map('trim', $words))));
  }
}
