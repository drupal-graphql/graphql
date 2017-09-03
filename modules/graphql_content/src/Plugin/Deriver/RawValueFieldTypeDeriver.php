<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\graphql_content\Plugin\GraphQL\Types\RawValueFieldType;

/**
 * Derive GraphQL types for raw values of drupal fields.
 */
class RawValueFieldTypeDeriver extends FieldFormatterDeriver {

  /**
   * {@inheritdoc}
   */
  protected function getDefinition($entityType, $bundle, array $displayOptions, FieldStorageDefinitionInterface $storage = NULL) {
    if (isset($storage)) {
      return [
        'name' => RawValueFieldType::getId($entityType, $storage->getName()),
      ];
    }

    return NULL;
  }

}
