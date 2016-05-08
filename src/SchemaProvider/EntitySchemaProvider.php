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
use Drupal\graphql\Utility\StringHelper;
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
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager service.
   * @param \Drupal\Core\TypedData\TypedDataManager $typedDataManager
   *   The typed data manager service.
   * @param \Drupal\graphql\TypeResolverInterface $typeResolver
   *   The type resolver service.
   */
  public function __construct(EntityManagerInterface $entityManager, TypedDataManager $typedDataManager, TypeResolverInterface $typeResolver) {
    $this->entityManager = $entityManager;
    $this->typeResolver = $typeResolver;
    $this->typedDataManager = $typedDataManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuerySchema() {
    $fields = [];

    // We only support content entity types for now.
    $types = array_filter($this->entityManager->getDefinitions(), function (EntityTypeInterface $entityType) {
      return $entityType->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface');
    });

    // Format the entity type names as camel-cased strings.
    $names = StringHelper::formatPropertyNameList(array_keys($types));

    foreach ($types as $key => $type) {
      /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinition $definition */
      $definition = $this->typedDataManager->createDataDefinition("entity:$key");
      if (!$resolved = $this->typeResolver->resolveRecursive($definition)) {
        continue;
      }

      $fields[$names[$key]] = [
        'type' => $resolved,
        'args' => [
          'id' => [
            'type' => new NonNullModifier(Type::intType()),
          ],
        ],
        'resolve' => [__CLASS__, 'getEntitySingle'],
        'resolveData' => ['type' => $key],
      ];

      $arguments = $this->getQueryArguments($definition);
      $argumentNames = StringHelper::formatPropertyNameList(array_keys($arguments));

      $fields["$names[$key]Query"] = [
        'type' => new ListModifier($resolved),
        'args' => [
          'offset' => ['type' => Type::intType()],
          'limit' => ['type' => Type::intType()],
        ] + array_combine($argumentNames, $arguments),
        'resolve' => [__CLASS__, 'getEntityList'],
        'resolveData' => ['type' => $key, 'args' => array_flip($argumentNames)],
      ];
    }

    return $fields;
  }

  /**
   * Utility function to retrieve the list of arguments for an entity query.
   *
   * @param \Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface $definition
   *   The entity type definition.
   *
   * @return array
   *   The list of arguments for potential use in the entity query.
   */
  protected function getQueryArguments(EntityDataDefinitionInterface $definition) {
    $args = [];
    foreach ($definition->getPropertyDefinitions() as $fieldName => $fieldDefinition) {
      if (!($fieldDefinition instanceof FieldDefinitionInterface)) {
        continue;
      }

      $storage = $fieldDefinition->getFieldStorageDefinition();
      if (!$storage->isQueryable()) {
        continue;
      };

      // Fetch the main property's definition and resolve it's type.
      $mainPropertyName = $storage->getMainPropertyName();
      $mainProperty = $storage->getPropertyDefinition($mainPropertyName);
      if (!$propertyType = $this->typeResolver->resolveRecursive($mainProperty)) {
        continue;
      }

      $wrappedType = $propertyType;

      // Extract the wrapped type of the main property.
      while ($wrappedType instanceof ModifierInterface) {
        $wrappedType = $wrappedType->getWrappedType();
      }

      // We only support scalars and enums as arguments.
      if (!($wrappedType instanceof ScalarType || $wrappedType instanceof EnumType)) {
        continue;
      }

      $args[$fieldName] = [
        'type' => new ListModifier($wrappedType),
        'description' => $fieldDefinition->getDescription(),
      ];
    }

    return $args;
  }

  /**
   * Single entity resolver callback.
   */
  public static function getEntitySingle($source, array $args = NULL, $root, Node $field, $a, $b, $c, $data) {
    // @todo Fix injection of container dependencies in resolver functions.
    $storage = \Drupal::entityManager()->getStorage($data['type']);
    if ($entity = $storage->load($args['id'])) {
      return $entity->getTypedData();
    }

    return NULL;
  }

  /**
   * Entity list resolver callback.
   */
  public static function getEntityList($source, array $args = NULL, $root, Node $field, $a, $b, $c, $data) {
    $storage = \Drupal::entityManager()->getStorage($data['type']);
    $query = $storage->getQuery()->accessCheck(TRUE);

    $rangeArgs = array('offset', 'limit');
    $filterArgs = array_diff_key($args, array_flip($rangeArgs));
    foreach ($filterArgs as $key => $arg) {
      if (isset($arg) && isset($data['args'][$key])) {
        $arg = is_array($arg) && sizeof($arg) === 1 ? reset($arg) : $arg;
        $operator = is_array($arg) ? 'IN' : '=';
        $query->condition($data['args'][$key], $arg, $operator);
      }
    }

    if (!empty($args['offset']) || !empty($args['limit'])) {
      $query->range($args['offset'] ?: NULL, $args['limit'] ?: NULL);
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
