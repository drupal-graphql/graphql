<?php

namespace Drupal\graphql_core;

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinition;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceDefinitionInterface;
use Drupal\graphql\Utility\StringHelper;

/**
 * GraphQL type mapper service.
 */
class TypeMapper {

  /**
   * Mapping of graphql types to drupal types.
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
   * Maps drupal data type to graphql type.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $dataDefinition
   *   The property definition.
   *
   * @return string
   */
  public function typedDataToGraphQLFieldType(DataDefinitionInterface $dataDefinition) {
    $dataType = $dataDefinition->getDataType();
    if ($dataDefinition instanceof DataReferenceDefinitionInterface) {
      $targetDefinition = $dataDefinition->getTargetDefinition();
      if ($targetDefinition instanceof EntityDataDefinition) {
        $entityType = $targetDefinition->getEntityTypeId();

        if ($this->entityTypeManager->getDefinition($entityType) instanceof ContentEntityType) {
          return StringHelper::camelCase($entityType);
        }

        // TODO Handle config entity references.
      }
    }

    foreach ($this->typeMap as $graphQlType => $typedDataTypes) {
      if (in_array($dataType, $typedDataTypes)) {
        return $graphQlType;
      }
    }

    return 'String';
  }

}
