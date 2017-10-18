<?php

namespace Drupal\graphql_core\Plugin\Deriver\Types;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\graphql_core\Plugin\Deriver\EntityFieldDeriverBase;
use Drupal\graphql_core\Plugin\GraphQL\Types\Entity\EntityFieldType;

/**
 * Derive GraphQL types for raw values of drupal fields.
 */
class EntityFieldTypeDeriver extends EntityFieldDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function getDerivativeDefinitionsFromFieldDefinition($entityTypeId, FieldStorageDefinitionInterface $fieldDefinition, array $basePluginDefinition) {
    if ($this->isSinglePropertyField($fieldDefinition)) {
      return [];
    }

    $fieldName = $fieldDefinition->getName();

    return [
      "$entityTypeId-$fieldName" => [
        'name' => EntityFieldType::getId($entityTypeId, $fieldName),
        'entity_type' => $entityTypeId,
        'field_name' => $fieldName,
      ] + $basePluginDefinition,
    ];
  }

}
