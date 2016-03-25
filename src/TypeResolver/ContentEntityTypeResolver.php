<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\ContentEntityTypeResolver.
 */

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinition;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\Field\TypedData\FieldItemDataDefinition;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Language\Node;

/**
 * Resolves typed data types.
 */
class ContentEntityTypeResolver implements TypeResolverInterface {
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
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a ContentEntityTypeResolver object.
   *
   * @param TypeResolverInterface $typeResolver
   *   The base type resolver service.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager service.
   */
  public function __construct(TypeResolverInterface $typeResolver, EntityManagerInterface $entityManager) {
    $this->typeResolver = $typeResolver;
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($type) {
    return $type instanceof EntityDataDefinitionInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRecursive($type) {
    // TODO: Implement resolveRecursive() method.
  }
}
