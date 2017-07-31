<?php

namespace Drupal\graphql_content;

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
   * @param string $typedDataType
   *   Drupal column type.
   *
   * @return string
   */
  public function typedDataToGraphQLFieldType($typedDataType) {
    foreach ($this->typeMap as $graphQlType => $typedDataTypes) {
      if (in_array($typedDataType, $typedDataTypes)) {
        return $graphQlType;
      }
    }

    return 'String';
  }

}
