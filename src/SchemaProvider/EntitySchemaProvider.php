<?php

namespace Drupal\graphql\SchemaProvider;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\graphql\GraphQL\Field\Root\Entity\EntityByIdField;
use Drupal\graphql\GraphQL\Field\Root\Entity\EntityByPathField;
use Drupal\graphql\GraphQL\Field\Root\Entity\EntityByUuidField;
use Drupal\graphql\SchemaProviderBase;
use Drupal\graphql\TypeResolverInterface;

/**
 * Generates a GraphQL Schema for content entity types.
 */
class EntitySchemaProvider extends SchemaProviderBase {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity manager service.
   * @param \Drupal\Core\TypedData\TypedDataManager $typedDataManager
   *   The typed data manager service.
   * @param \Drupal\graphql\TypeResolverInterface $typeResolver
   *   The type resolver service.
   */
  public function __construct(EntityTypeManager $entityTypeManager, TypedDataManager $typedDataManager, TypeResolverInterface $typeResolver) {
    $this->entityTypeManager = $entityTypeManager;
    $this->typeResolver = $typeResolver;
    $this->typedDataManager = $typedDataManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['entity_field_info', 'entity_bundles'];
  }

  /**
   * {@inheritdoc}
   */
  public function getQuerySchema() {
    $entityTypes = array_map(function (EntityTypeInterface $entityType) {
      $entityTypeId = $entityType->id();
      $dataDefinition = $this->typedDataManager->createDataDefinition("entity:$entityTypeId");
      return $this->typeResolver->resolveRecursive($dataDefinition);
    }, $this->entityTypeManager->getDefinitions());

    $entityTypeKeys = array_keys($entityTypes);

    $fields = array_reduce($entityTypeKeys, function ($carry, $key) use ($entityTypes) {
      return array_merge($carry, [
        new EntityByIdField($key, $entityTypes[$key]),
        new EntityByUuidField($key, $entityTypes[$key]),
      ]);
    }, []);

    return array_merge($fields, [
      new EntityByPathField(),
    ]);
  }
}
