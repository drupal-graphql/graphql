<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\graphql_content\Plugin\GraphQL\Types\RawFieldValueType;

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
  protected function getFieldPluginDefinition($basePluginDefinition, EntityTypeInterface $type, $bundle, $field, $storage) {
    if ($storage) {
      $this->derivatives["{$type->id()}-$bundle-$field"] = [
        'type' => RawFieldValueType::getId($storage),
      ] + $basePluginDefinition;
    }
  }

}
