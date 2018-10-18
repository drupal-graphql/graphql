<?php

namespace Drupal\graphql_core\Plugin\Deriver\Types;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\Deriver\EntityFieldDeriverBase;

/**
 * Derive GraphQL types for raw values of drupal fields.
 */
class EntityFieldTypeDeriver extends EntityFieldDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function getDerivativeDefinitionsFromFieldDefinition(FieldDefinitionInterface $fieldDefinition, array $basePluginDefinition) {
    $itemDefinition = $fieldDefinition->getItemDefinition();
    if (!($itemDefinition instanceof ComplexDataDefinitionInterface) || !$propertyDefinitions = $itemDefinition->getPropertyDefinitions()) {
      return [];
    }

    $propertyDefinitions = $itemDefinition->getPropertyDefinitions();
    if (count($propertyDefinitions) <= 1) {
      return [];
    }

    $entityTypeId = $fieldDefinition->getTargetEntityTypeId();
    $entityType = $this->entityTypeManager->getDefinition($entityTypeId);
    $supportsBundles = $entityType->hasKey('bundle');
    $fieldName = $fieldDefinition->getName();
    $fieldBundle = $fieldDefinition->getTargetBundle() ?: '';

    return ["$entityTypeId-$fieldName-$fieldBundle" => [
      'name' => StringHelper::camelCase('field', $entityTypeId, $supportsBundles ? $fieldBundle : '', $fieldName),
      'description' => $fieldDefinition->getDescription(),
      'entity_type' => $entityTypeId,
      'entity_bundle' => $fieldBundle ?: NULL,
      'field_name' => $fieldName,
    ] + $basePluginDefinition];
  }

}
