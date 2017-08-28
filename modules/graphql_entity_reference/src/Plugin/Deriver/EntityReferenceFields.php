<?php

namespace Drupal\graphql_entity_reference\Plugin\Deriver;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_content\Plugin\Deriver\FieldFormatterDeriver;

/**
 * Derive fields from entity reference view fields.
 */
class EntityReferenceFields extends FieldFormatterDeriver {

  /**
   * {@inheritdoc}
   */
  protected function getDefinition($entityType, $bundle, array $displayOptions, FieldStorageDefinitionInterface $storage = NULL) {
    if (isset($storage)) {
      return [
        'type' => StringHelper::camelCase($storage->getSetting('target_type')),
      ] + parent::getDefinition($entityType, $bundle, $displayOptions, $storage);
    }

    return NULL;
  }

}
