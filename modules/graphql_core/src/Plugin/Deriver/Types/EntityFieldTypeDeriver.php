<?php

namespace Drupal\graphql_core\Plugin\Deriver\Types;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\Deriver\EntityFieldDeriverBase;

/**
 * Derive GraphQL types for raw values of drupal fields.
 */
class EntityFieldTypeDeriver extends EntityFieldDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function getDerivativeDefinitionsFromFieldDefinition($entityTypeId, FieldStorageDefinitionInterface $fieldDefinition, array $basePluginDefinition) {
    // Only create a type for fields with at least two properties.
    $propertyDefinitions = $fieldDefinition->getPropertyDefinitions();
    if (count($propertyDefinitions) <= 1) {
      return [];
    }

    $fieldName = $fieldDefinition->getName();
    return ["$entityTypeId-$fieldName" => [
      'name' => StringHelper::camelCase('field', $entityTypeId, $fieldName),
      'description' => $fieldDefinition->getDescription(),
      'entity_type' => $entityTypeId,
      'field_name' => $fieldName,
    ] + $basePluginDefinition];
  }

}
