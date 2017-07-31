<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\graphql_content\Plugin\GraphQL\Types\RawValueFieldType;

/**
 * Deriver for RawValue fields.
 */
class RawValueFieldDeriver extends FieldFormatterDeriver {

  /**
   * Provide plugin definition values from field storage and display options.
   *
   * @param string $entityType
   *   The host entity type.
   * @param string $bundle
   *   The host entity bundle.
   * @param array $displayOptions
   *   Array of display options.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface|null $storage
   *   Field storage definition object.
   *
   * @return array
   *   Associative array of additional plugin definition values.
   */
  protected function getDefinition($entityType, $bundle, array $displayOptions, FieldStorageDefinitionInterface $storage = NULL) {
    return [
      'types' => [
        graphql_core_camelcase([$entityType, $bundle]),
      ],
      'name' => graphql_core_propcase($storage->getName()),
      'virtual' => !$storage,
      'multi' => $storage ? $storage->getCardinality() != 1 : FALSE,
      'nullable' => TRUE,
      'field' => $storage->getName(),
      'type' => RawValueFieldType::getId($entityType, $storage->getName()),
    ];
  }

}
