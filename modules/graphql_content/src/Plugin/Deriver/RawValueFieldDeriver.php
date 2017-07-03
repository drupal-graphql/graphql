<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\graphql_content\Plugin\GraphQL\Types\RawValueFieldType;

class RawValueFieldDeriver extends FieldDeriverBase {

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

      // Add the parent field, eg. 'body'.
      $this->derivatives["{$type->id()}-$fieldName"] = [
        'type' => $dataType,
      ] + $basePluginDefinition;
    }
  }

}
