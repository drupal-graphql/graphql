<?php

namespace Drupal\graphql_core\Plugin\Deriver\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceDefinitionInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\Deriver\EntityFieldDeriverBase;

class EntityFieldItemDeriver extends EntityFieldDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function getDerivativeDefinitionsFromFieldDefinition(FieldDefinitionInterface $fieldDefinition, array $basePluginDefinition) {
    $itemDefinition = $fieldDefinition->getItemDefinition();
    if (!($itemDefinition instanceof ComplexDataDefinitionInterface) || !$propertyDefinitions = $itemDefinition->getPropertyDefinitions()) {
      return [];
    }

    if (count($propertyDefinitions) <= 1) {
      return [];
    }

    $tags = array_merge($fieldDefinition->getCacheTags(), ['entity_field_info']);
    $contexts = $fieldDefinition->getCacheContexts();
    $maxAge = $fieldDefinition->getCacheMaxAge();

    $entityTypeId = $fieldDefinition->getTargetEntityTypeId();
    $entityType = $this->entityTypeManager->getDefinition($entityTypeId);
    $supportsBundles = $entityType->hasKey('bundle');
    $fieldName = $fieldDefinition->getName();
    $fieldBundle = $fieldDefinition->getTargetBundle() ?: '';

    $commonDefinition = [
      'parents' => [StringHelper::camelCase('field', $entityTypeId, $supportsBundles ? $fieldBundle : '', $fieldName)],
      'schema_cache_tags' => $tags,
      'schema_cache_contexts' => $contexts,
      'schema_cache_max_age' => $maxAge,
    ] + $basePluginDefinition;

    $derivatives = [];
    foreach ($propertyDefinitions as $property => $propertyDefinition) {
      $derivatives["$entityTypeId-$fieldName-$fieldBundle-$property"] = [
        'name' => StringHelper::propCase($property),
        'description' => $propertyDefinition->getDescription(),
        'property' => $property,
        'type' => $this->extractDataType($propertyDefinition),
      ] + $commonDefinition;
    }

    return $derivatives;
  }

  /**
   * Extracts the data type of a property's data definition.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $propertyDefinition
   *   The property's data definition.
   *
   * @return string
   *   The property's data type.
   */
  protected function extractDataType(DataDefinitionInterface $propertyDefinition) {
    if ($propertyDefinition instanceof DataReferenceDefinitionInterface) {
      return $propertyDefinition->getTargetDefinition()->getDataType();
    }

    return $propertyDefinition->getDataType();
  }

}
