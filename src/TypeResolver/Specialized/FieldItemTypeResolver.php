<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\Specialized\FieldItemTypeResolver.
 */

namespace Drupal\graphql\TypeResolver\Specialized;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\TypedData\FieldItemDataDefinition;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\graphql\TypeResolver\Generic\ComplexDataTypeResolver;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Language\Node;

/**
 * Resolves typed data types.
 */
class FieldItemTypeResolver extends ComplexDataTypeResolver {
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
   * Constructs a FieldItemTypeResolver object.
   *
   * @param TypeResolverInterface $type_resolver
   *   The base type resolver service.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   */
  public function __construct(TypeResolverInterface $type_resolver, EntityManagerInterface $entity_manager) {
    parent::__construct($type_resolver);
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($type) {
    return $type instanceof FieldItemDataDefinition;
  }

  /**
   * {@inheritdoc}
   */
  protected function getTypeIdentifier(ComplexDataDefinitionInterface $definition) {
    /** @var FieldItemDataDefinition $definition */
    $field_definition = $definition->getFieldDefinition();
    $entity_type_id = $field_definition->getTargetEntityTypeId();
    return "field:item:$entity_type_id:{$field_definition->getName()}";
  }
}
