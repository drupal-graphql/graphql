<?php

/**
 * @file
 * Contains \Drupal\graphql\SchemaProvider\EntitySchemaProvider.
 */

namespace Drupal\graphql\SchemaProvider;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\graphql\TypeResolverInterface;
use Drupal\graphql\Utility\String;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\EnumType;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\ModifierInterface;
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
   * The type resolver service.
   *
   * @var \Drupal\graphql\TypeResolverInterface
   */
  protected $typeResolver;

  /**
   * The typed data manager service.
   *
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
    $fields = [];

    // We only support content entity types for now.
    $types = array_filter($this->entityManager->getDefinitions(), function (EntityTypeInterface $entity_type) {
      return $entity_type->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface');
    });

    // Format the entity type names as camel-cased strings.
    $names = String::formatPropertyNameList(array_keys($types));

    foreach ($types as $key => $type) {
      /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinition $definition */
      $definition = $this->typedDataManager->createDataDefinition("entity:$key");
      if (!$resolved = $this->typeResolver->resolveRecursive($definition)) {
        continue;
      }

      $fields['id'][$names[$key]] = [
        'type' => $resolved,
        'args' => [
          'id' => [
            'type' => Type::intType(),
            'required' => TRUE,
          ],
        ],
        'resolve' => [__CLASS__, 'resolveFromId'],
        'resolveData' => ['type' => $key],
      ];

      $fields['query'][$names[$key]] = [
        'type' => new ListModifier($resolved),
        'args' => $this->getQueryArguments($definition),
        'resolve' => [__CLASS__, 'resolveFromEntityQuery'],
        'resolveData' => ['type' => $key],
      ];
    }

    return !empty($fields) ? [
      'entity' => [
        'type' => new ObjectType('__EntityRoot', $fields['id']),
        'resolve' => [__CLASS__, 'resolveRoot']
      ],
      'entityQuery' => [
        'type' => new ObjectType('__EntityQueryRoot', $fields['query']),
        'resolve' => [__CLASS__, 'resolveRoot']
      ],
    ] : [];
  }

  /**
   * @param \Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface $definition
   *
   * @return array
   */
  protected function getQueryArguments(EntityDataDefinitionInterface $definition) {
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
      $main_property = $storage->getPropertyDefinition($main_property_name);
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

    return $args;
  }

  /**
   * @return bool
   */
  public static function resolveRoot() {
    return TRUE;
  }

  /**
   * @param $source
   * @param array|NULL $args
   * @param $root
   * @param \Fubhy\GraphQL\Language\Node $field
   *
   * @return array
   */
  public static function resolveFromId($source, array $args = NULL, $root, Node $field, $a, $b, $c, $data) {
    // @todo Fix injection of container dependencies in resolver functions.
    $storage = \Drupal::entityManager()->getStorage($data['type']);
    if ($entity = $storage->load($args['id'])) {
      return $entity->getTypedData();
    }

    return NULL;
  }

  /**
   * @param $source
   * @param array|NULL $args
   * @param $root
   * @param \Fubhy\GraphQL\Language\Node $field
   *
   * @return array
   */
  public static function resolveFromEntityQuery($source, array $args = NULL, $root, Node $field, $a, $b, $c, $data) {
    $storage = \Drupal::entityManager()->getStorage($data['type']);
    $query = $storage->getQuery()
      ->accessCheck(TRUE)
      // @todo Make this configurable and expose it as an argument.
      ->range(0, 10);

    foreach ($args as $key => $arg) {
      if (isset($arg)) {
        $operator = is_array($arg) ? 'IN' : '=';
        $query->condition($key, $arg, $operator);
      }
    }

    $result = $query->execute();
    if (!empty($result)) {
      $entities = $storage->loadMultiple($result);

      // Filter entities that the current user doesn't have view access for.
      return array_map(function (ContentEntityInterface $entity) {
        return $entity->getTypedData();
      }, array_filter($entities, function (ContentEntityInterface $entity) {
        return $entity->access('view');
      }));
    }

    return [];
  }
}
