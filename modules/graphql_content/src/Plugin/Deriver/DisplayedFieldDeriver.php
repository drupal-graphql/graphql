<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Derive GraphQL fields for all fields exposed in graphql display modes.
 */
class DisplayedFieldDeriver extends FieldDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function isFieldSupported($displaySettings) {
    return !isset($displaySettings['type'])
      || $displaySettings['type'] !== 'raw_value';
  }

}
