<?php

namespace Drupal\graphql_entity_reference\Plugin\Deriver;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\graphql_content\Plugin\Deriver\FieldFormatterDeriver;

/**
 * Derive fields from entity reference view fields.
 */
class EntityReferenceFields extends FieldFormatterDeriver {

  /**
   * {@inheritdoc}
   */
  protected function getDefinition($entityType, $bundle, array $displayOptions, FieldStorageDefinitionInterface $storage = NULL) {
    return [
      'type' => graphql_core_camelcase($storage->getSetting('target_type')),
    ] + parent::getDefinition($entityType, $bundle, $displayOptions, $storage);
  }

}
