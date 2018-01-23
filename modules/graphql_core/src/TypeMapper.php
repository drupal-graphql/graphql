<?php

namespace Drupal\graphql_core;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinition;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceDefinitionInterface;
use Drupal\graphql\Utility\StringHelper;

class TypeMapper {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Mapping of graphql types to drupal types.
   *
   * @var array
   */
  protected $typeMap;

  /**
   * TypeMapper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param array $typeMap
   *   The mapping of graphql types to drupal types.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, array $typeMap) {
    $this->entityTypeManager = $entityTypeManager;
    $this->typeMap = $typeMap;
  }

  /**
   * Maps a drupal data type to a graphql type.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $dataDefinition
   *   The property definition.
   *
   * @return string|null
   *   The name of the determined type.
   */
  public function getTypeFromDataDefinition(DataDefinitionInterface $dataDefinition) {
    if ($dataDefinition instanceof DataReferenceDefinitionInterface) {
      $targetDefinition = $dataDefinition->getTargetDefinition();
      if ($targetDefinition instanceof EntityDataDefinition) {
        $entityType = $targetDefinition->getEntityTypeId();
        if ($entityType !== NULL && $this->entityTypeManager->hasDefinition($entityType)) {
          return StringHelper::camelCase($entityType);
        }

        return 'Entity';
      }
    }

    $dataType = $dataDefinition->getDataType();
    foreach ($this->typeMap as $type => $typedDataTypes) {
      if (in_array($dataType, $typedDataTypes)) {
        return $type;
      }
    }

    return NULL;
  }

}
