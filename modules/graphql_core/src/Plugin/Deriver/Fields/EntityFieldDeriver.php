<?php

namespace Drupal\graphql_core\Plugin\Deriver\Fields;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\Deriver\EntityFieldDeriverBase;

class EntityFieldDeriver extends EntityFieldDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function getDerivativeDefinitionsFromFieldDefinition($entityTypeId, FieldStorageDefinitionInterface $fieldDefinition, array $basePluginDefinition) {
    if (!$propertyDefinitions = $fieldDefinition->getPropertyDefinitions()) {
      return [];
    }

    $fieldName = $fieldDefinition->getName();
    if (!$parents = $this->getParentsForField($entityTypeId, $fieldDefinition)) {
      return [];
    }

    $tags = array_merge($fieldDefinition->getCacheTags(), ['entity_field_info']);
    $maxAge = $fieldDefinition->getCacheMaxAge();
    $contexts = $fieldDefinition->getCacheContexts();

    $derivative = [
      'parents' => $parents,
      'name' => StringHelper::propCase($fieldName),
      'description' => $fieldDefinition->getDescription(),
      'field' => $fieldName,
      'schema_cache_tags' => $tags,
      'schema_cache_contexts' => $contexts,
      'schema_cache_max_age' => $maxAge,
      'response_cache_tags' => $tags,
      'response_cache_contexts' => $contexts,
      'response_cache_max_age' => $maxAge,
    ] + $basePluginDefinition;

    if (count($propertyDefinitions) === 1) {
      // Flatten the structure for single-property fields.
      $derivative['type'] = reset($propertyDefinitions)->getDataType();
      $derivative['property'] = key($propertyDefinitions);
    }
    else {
      $derivative['type'] = StringHelper::camelCase('field', $entityTypeId, $fieldName);
    }

    if ($fieldDefinition->isMultiple()) {
      $derivative['type'] = StringHelper::listType($derivative['type']);
    }

    return ["$entityTypeId-$fieldName" => $derivative];
  }

  /**
   * Determines the parent types for a field.
   *
   * @param string $entityTypeId
   *   The entity type id of the field.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $fieldDefinition
   *   The field storage definition.
   *
   * @return array
   *   The pareants of the field.
   */
  protected function getParentsForField($entityTypeId, FieldStorageDefinitionInterface $fieldDefinition) {
    if ($fieldDefinition->isBaseField()) {
      return [StringHelper::camelCase($entityTypeId)];
    }

    if ($fieldDefinition instanceof FieldStorageConfigInterface) {
      $targetType = $this->entityTypeManager->getDefinition($fieldDefinition->getTargetEntityTypeId());
      if ($targetType->hasKey('bundle')) {
        return array_values(array_map(function ($bundleId) use ($entityTypeId) {
          return StringHelper::camelCase($entityTypeId, $bundleId);
        }, $fieldDefinition->getBundles()));
      }
      else {
        return [StringHelper::camelCase($entityTypeId)];
      }
    }

    return [];
  }
}
