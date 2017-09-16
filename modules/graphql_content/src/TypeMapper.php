<?php

namespace Drupal\graphql_content;

use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceDefinitionInterface;
use Drupal\graphql\Utility\StringHelper;

class TypeMapper {

  /**
   * Mapping of graphql types to drupal types.
   *
   * @var array
   */
  protected $typeMap;

  /**
   * TypeMapper constructor.
   *
   * @param array $typeMap
   *   The mapping of graphql types to drupal types.
   */
  public function __construct(array $typeMap) {
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
    if ($dataDefinition instanceof DataReferenceDefinitionInterface) {
      $targetDefinition = $dataDefinition->getTargetDefinition();
      $entityType = $targetDefinition->getEntityTypeId();

      return StringHelper::camelCase($entityType);
    }

    foreach ($this->typeMap as $graphQlType => $typedDataTypes) {
      if (in_array($dataDefinition->getDataType(), $typedDataTypes)) {
        return $graphQlType;
      }
    }

    return 'String';
  }

}
