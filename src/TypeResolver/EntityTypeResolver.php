<?php

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\graphql\GraphQL\Type\Entity\EntityInterfaceType;
use Drupal\graphql\GraphQL\Type\Entity\EntityObjectType;
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
   * @param TypeResolverInterface $typeResolver
   *   The base type resolver service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityManager
   *   The entity type manager service.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typedDataManager
   *   The typed data manager service.
   */
  public function __construct(EntityTypeManager $entityManager, TypedDataManagerInterface $typedDataManager, TypeResolverInterface $typeResolver) {
    $this->typeResolver = $typeResolver;
    $this->entityTypeManager = $entityManager;
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
    return new EntityInterfaceType();
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
    // TODO: Implement canResolveRelayNode() method.
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRelayNode($type, $id) {
    // TODO: Implement resolveRelayNode() method.
  }

  /**
   * {@inheritdoc}
   */
  public function canResolveRelayType($object) {
    // TODO: Implement canResolveRelayType() method.
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRelayType($object) {
    // TODO: Implement resolveRelayType() method.
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