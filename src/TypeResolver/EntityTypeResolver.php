<?php

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\graphql\GraphQL\Type\Entity\EntityInterfaceType;
use Drupal\graphql\GraphQL\Type\Entity\EntityObjectType;
use Drupal\graphql\GraphQL\Type\Entity\EntitySpecificInterfaceType;
use Drupal\graphql\GraphQL\Type\EntityType\EntityTypeObjectType;
use Youshido\GraphQL\Relay\Node;

class EntityTypeResolver implements TypeResolverWithRelaySupportInterface {
  /**
   * The typed data manager service.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The type resolver service.
   *
   * @var \Drupal\graphql\TypeResolver\TypeResolverInterface
   */
  protected $typeResolver;

  /**
   * Constructs a ContentEntityTypeResolver object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typedDataManager
   *   The typed data manager service.
   * @param TypeResolverInterface $typeResolver
   *   The base type resolver service.
   */
  public function __construct(EntityTypeManager $entityTypeManager, TypedDataManagerInterface $typedDataManager, TypeResolverInterface $typeResolver) {
    $this->typeResolver = $typeResolver;
    $this->entityTypeManager = $entityTypeManager;
    $this->typedDataManager = $typedDataManager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(DataDefinitionInterface $definition) {
    return $definition instanceof EntityDataDefinitionInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRecursive(DataDefinitionInterface $type) {
    if (!($type instanceof EntityDataDefinitionInterface)) {
      return NULL;
    }

    if (!$entityTypeId = $type->getEntityTypeId()) {
      return new EntityInterfaceType();
    }

    $entityType = $this->entityTypeManager->getDefinition($entityTypeId);
    if (($constraintBundles = $type->getBundles()) === NULL) {
      // Return the interface type (all bundles are possible).
      return new EntitySpecificInterfaceType($entityType);
    }
    else if (count($constraintBundles) === 1) {
      // Return the object type (only a single bundle is possible)
      return new EntityObjectType($entityType, reset($constraintBundles));
    }

    // Multiple bundles are possible.
    // @todo Maybe we should return a union type here.
    return new EntitySpecificInterfaceType($entityType);
  }

  /**
   * {@inheritdoc}
   */
  public function collectTypes() {
    return [new EntityTypeObjectType()];
  }

  /**
   * {@inheritdoc}
   */
  public function canResolveRelayNode($type, $id) {
    list($prefix, $entityTypeId) = explode('/', $type);
    if ($prefix === 'entity' && $this->entityTypeManager->hasDefinition($entityTypeId)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRelayNode($type, $id) {
    list(, $entityTypeId) = explode('/', $type);
    $entityStorage = $this->entityTypeManager->getStorage($entityTypeId);
    return $entityStorage->load($id);
  }

  /**
   * {@inheritdoc}
   */
  public function canResolveRelayType($object) {
    return $object instanceof EntityInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRelayType($object) {
    if ($object instanceof EntityInterface) {
      return $this->typeResolver->resolveRecursive($object->getTypedData()->getDataDefinition());
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function canResolveRelayGlobalId($type, $value) {
    return $value instanceof EntityInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRelayGlobalId($type, $value) {
    if ($value instanceof EntityInterface) {
      return Node::toGlobalId($type, $value->id());
    }

    return NULL;
  }
}