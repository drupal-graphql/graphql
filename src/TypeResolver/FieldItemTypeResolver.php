<?php

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\TypedData\FieldItemDataDefinition;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Language\Node;

/**
 * Resolves the schema for field definitions.
 *
 * Note: This resolver is only relevant for complex fields which can not be
 * simplified via the content entity schema resolver.
 */
class FieldItemTypeResolver extends TypedDataTypeResolver {
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
   * @param TypeResolverInterface $typeResolver
   *   The base type resolver service.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager service.
   */
  public function __construct(TypeResolverInterface $typeResolver, EntityManagerInterface $entityManager) {
    parent::__construct($typeResolver);
    $this->entityManager = $entityManager;
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
  protected function getTypeIdentifier(DataDefinitionInterface $type) {
    /** @var FieldItemDataDefinition $type */
    $fieldDefinition = $type->getFieldDefinition();
    $fieldName = $fieldDefinition->getName();
    $entityTypeId = $fieldDefinition->getTargetEntityTypeId();

    // Create a more specifc data type identifier because the one returned via
    // typed data is too vague and does not contain information about the owning
    // entity type.
    return "field:item:$entityTypeId:$fieldName";
  }
}
