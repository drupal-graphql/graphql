<?php

/**
 * @file
 * Contains \Drupal\graphql\SchemaProvider\EntitySchemaProvider.
 */

namespace Drupal\graphql\SchemaProvider;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
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
      $definition = $this->typedDataManager->createDataDefinition("entity:$entity_type_id");

      $fields[$entity_type_id] = [
        'type' => new ListModifier($this->typeResolver->resolveRecursive($definition)),
        'args' => [
          'id' => ['type' => new ListModifier(new NonNullModifier(Type::idType()))],
        ],
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
    if ($source instanceof EntityManagerInterface && isset($args['id'])) {
      $storage = $source->getStorage($field->get('name')->get('value'));

      return array_map(function ($entity) {
        return $entity->getTypedData();
      }, $storage->loadMultiple($args['id']));
    }
  }
}
