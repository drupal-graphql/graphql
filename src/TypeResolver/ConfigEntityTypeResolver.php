<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\ConfigEntityTypeResolver.
 */

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Language\Node;

/**
 * Resolves typed data types.
 */
class ConfigEntityTypeResolver implements TypeResolverInterface {
  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a ConfigEntityTypeResolver object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager service.
   */
  public function __construct(EntityManagerInterface $entityManager) {
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($type) {
    if ($type instanceof EntityDataDefinitionInterface) {
      $entityTypeId = $type->getEntityTypeId();
      $entityType = $this->entityManager->getDefinition($entityTypeId);

      return $entityType->isSubclassOf('\Drupal\Core\Config\Entity\ConfigEntityInterface');
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRecursive($type) {
    // @todo We do not currently support config entities.
    return NULL;
  }
}
