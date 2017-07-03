<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\graphql_content\Plugin\GraphQL\Types\RawValueFieldType;

class RawValueFieldItemDeriver extends FieldDeriverBase {

  // TODO Provide more mappings.

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
   * {@inheritdoc}
   */
  protected function isFieldSupported($displaySettings) {
    return isset($displaySettings['type'])
      && $displaySettings['type'] === 'raw_value';
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldPluginDefinition($basePluginDefinition, EntityTypeInterface $type, $bundle, $fieldName, $storage) {
    /** @var \Drupal\field\Entity\FieldStorageConfig $storage */

    // Base fields are not handled at the moment.
    if ($storage) {
      // Object type in GraphQL, eg. NodeBodyRawValue.
      $dataType = RawValueFieldType::getId($type->id(), $fieldName);

      // Add the subfields, eg. value, summary.
      foreach ($storage->getSchema()['columns'] as $columnName => $schema) {
        $this->derivatives["{$type->id()}-$fieldName-$columnName"] = [
          'name' => graphql_core_propcase($columnName),
          'schemaColumn' => $columnName,
          'multi' => FALSE,
          'type' => $this->typedDataToGraphQLFieldType($schema['type']),
          'types' => [$dataType],
        ] + $basePluginDefinition;
      }

      // TODO Investigate the feasibility of exposing the options array for link field.
    }
  }

  /**
   * Maps drupal data type to graphql type.
   *
   * @param string $typedDataType
   *   Drupal column type.
   *
   * @return string
   * @throws \Drupal\graphql_content\Plugin\Deriver\TypeMappingNotFoundException
   */
  protected function typedDataToGraphQLFieldType($typedDataType) {
    foreach (static::$mapping as $graphQlType => $typedDataTypes) {
      if (in_array($typedDataType, $typedDataTypes)) {
        return $graphQlType;
      }
    }

    throw new TypeMappingNotFoundException("No mapping found for typed data type '$typedDataType'");
  }

}

class TypeMappingNotFoundException extends \Exception {};
