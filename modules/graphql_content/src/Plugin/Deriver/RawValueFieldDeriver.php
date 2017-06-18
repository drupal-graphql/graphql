<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Core\Entity\EntityTypeInterface;

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
  protected function getFieldPluginDefinition($basePluginDefinition, EntityTypeInterface $type, $bundle, $field) {
    $this->derivatives[$type->id() . '-' . $bundle . '-' . $field] = [
      'type' => 'String',
    ] + $basePluginDefinition;
  }

}
