<?php

namespace Drupal\graphql_core\Plugin\Deriver\Fields;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceDefinitionInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\Deriver\EntityFieldDeriverBase;

class EntityFieldItemDeriver extends EntityFieldDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function getDerivativeDefinitionsFromFieldDefinition($entityTypeId, FieldStorageDefinitionInterface $fieldDefinition, array $basePluginDefinition, $bundleId = NULL) {
    if (!$propertyDefinitions = $fieldDefinition->getPropertyDefinitions()) {
      return [];
    }

    $tags = array_merge($fieldDefinition->getCacheTags(), ['entity_field_info']);
    $contexts = $fieldDefinition->getCacheContexts();
    $maxAge = $fieldDefinition->getCacheMaxAge();

    $fieldName = $fieldDefinition->getName();
    $commonDefinition = [
      'parents' => [StringHelper::camelCase('field', $entityTypeId, $fieldName)],
      'schema_cache_tags' => $tags,
      'schema_cache_contexts' => $contexts,
      'schema_cache_max_age' => $maxAge,
      'response_cache_tags' => $tags,
      'response_cache_contexts' => $contexts,
      'response_cache_max_age' => $maxAge,
    ] + $basePluginDefinition;

    $derivatives = [];
    foreach ($propertyDefinitions as $property => $propertyDefinition) {
      $derivatives["$entityTypeId-$fieldName-$property"] = [
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
