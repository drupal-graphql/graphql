<?php

namespace Drupal\graphql_content;

class TypeMatcher {

  /**
   * Mapping of graphql types to drupal types.
   *
   * @var array
   */
  protected static $mapping = [
    'String' => [
      'text',
      'varchar',
      'varchar_ascii',
      'blob',
    ],
    'Int' => [
      'int',
    ],
    'Float' => [
      'numeric',
      'float',
    ]
  ];

  /**
   * Maps drupal data type to graphql type.
   *
   * @param string $typedDataType
   *   Drupal column type.
   *
   * @return string
   * @throws \Drupal\graphql_content\Plugin\Deriver\TypeMappingNotFoundException
   */
  public function typedDataToGraphQLFieldType($typedDataType) {
    foreach (static::$mapping as $graphQlType => $typedDataTypes) {
      if (in_array($typedDataType, $typedDataTypes)) {
        return $graphQlType;
      }
    }

    return 'String';
  }

}
