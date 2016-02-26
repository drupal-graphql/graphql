<?php

/**
 * @file
 * Contains \Drupal\graphql\SchemaProvider\EntitySchemaProvider.
 */

namespace Drupal\graphql\SchemaProvider;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\field\Tests\FieldDefinitionIntegrityTest;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\EnumType;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\ModifierInterface;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\ScalarType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Generates a GraphQL Schema for content entity types.
 */
class EntitySchemaProvider extends SchemaProviderBase {
  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var \Drupal\graphql\TypeResolverInterface
   */
  protected $typeResolver;

  /**
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * Constructs a EntitySchemaProvider object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   The typed data manager service.
   * @param \Drupal\graphql\TypeResolverInterface $type_resolver
   *   The type resolver service.
   */
  public function __construct(EntityManagerInterface $entity_manager, TypedDataManager $typed_data_manager, TypeResolverInterface $type_resolver) {
    $this->entityManager = $entity_manager;
    $this->typeResolver = $type_resolver;
    $this->typedDataManager = $typed_data_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuerySchema() {
    // We only support content entity types for now.
    $entity_types = array_filter($this->entityManager->getDefinitions(), function (EntityTypeInterface $entity_type) {
      return $entity_type->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface');
    });

    $fields = [];
    foreach ($entity_types as $entity_type_id => $entity_type) {
      /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinition $definition */
      $definition = $this->typedDataManager->createDataDefinition("entity:$entity_type_id");

      $args = [];
      foreach ($definition->getPropertyDefinitions() as $field_name => $field_definition) {
        if (!($field_definition instanceof FieldDefinitionInterface)) {
          continue;
        }

        $storage = $field_definition->getFieldStorageDefinition();
        if (!$storage->isQueryable()) {
          continue;
        };

        // Fetch the main property's definition and resolve it's type.
        $main_property_name = $storage->getMainPropertyName();
        $main_property = $field_definition->getPropertyDefinition($main_property_name);
        $property_type = $this->typeResolver->resolveRecursive($main_property);
        $wrapped_type = $property_type;

        // Extract the wrapped type of the main property.
        while ($wrapped_type instanceof ModifierInterface) {
          $wrapped_type = $wrapped_type->getWrappedType();
        }

        // We only support scalars and enums as arguments.
        if (!($wrapped_type instanceof ScalarType || $wrapped_type instanceof EnumType)) {
          continue;
        }

        $args[$field_name] = [
          'type' => new ListModifier($wrapped_type),
          'name' => $field_definition->getName(),
          'description' => $field_definition->getDescription(),
        ];
      }

      $fields[$entity_type_id] = [
        'type' => new ListModifier($this->typeResolver->resolveRecursive($definition)),
        'args' => $args,
        'resolve' => [__CLASS__, 'resolveEntity'],
      ];
    }

    return !empty($fields) ? ['entity' => [
      'type' => new ObjectType('__EntityRoot', $fields),
      'resolve' => function () {
        return $this->entityManager;
      }
    ]] : [];
  }

  /**
   * @param $source
   * @param array|NULL $args
   * @param $root
   * @param \Fubhy\GraphQL\Language\Node $field
   *
   * @return array
   */
  public static function resolveEntity($source, array $args = NULL, $root, Node $field) {
    if ($source instanceof EntityManagerInterface) {
      $args = array_filter($args, function ($arg) {
        return isset($arg);
      });

      if (empty($args)) {
        return [];
      }

      $storage = $source->getStorage($field->get('name')->get('value'));
      $query = $storage->getQuery()
        ->accessCheck(TRUE)
        ->range(0, 10);

      foreach ($args as $key => $arg) {
        if (isset($arg)) {
          $query->condition($key, $arg, 'IN');
        }
      }

      if ($ids = $query->execute()) {
        return array_map(function ($entity) {
          return $entity->getTypedData();
        }, $storage->loadMultiple($ids));
      }

      return [];
    }
  }
}
