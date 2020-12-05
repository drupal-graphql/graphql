<?php

namespace Drupal\graphql_resolver_builder_test\TypedData\Definition;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Data definition for the Tree data type.
 */
class TreeDefinition extends ComplexDataDefinitionBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {
    if (!isset($this->propertyDefinitions)) {
      $info = &$this->propertyDefinitions;

      $info['left'] = self::create('tree')
        ->setLabel('Left branch');

      $info['right'] = self::create('tree')
        ->setLabel('Right branch');

      $info['value'] = DataDefinition::create('string')
        ->setLabel('Leaf value');
    }
    return $this->propertyDefinitions;
  }

}
