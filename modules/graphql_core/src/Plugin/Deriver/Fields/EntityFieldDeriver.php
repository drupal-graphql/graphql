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
    $fieldName = $fieldDefinition->getName();
    if (!$parents = $this->getParentsForField($entityTypeId, $fieldDefinition)) {
      return [];
    }

    $derivative = [
      'parents' => $parents,
      'name' => StringHelper::propCase($fieldName),
      'description' => $fieldDefinition->getDescription(),
      'field' => $fieldName,
      'schema_cache_tags' => array_merge($fieldDefinition->getCacheTags(), ['entity_field_info']),
      'schema_cache_contexts' => $fieldDefinition->getCacheContexts(),
      'schema_cache_max_age' => $fieldDefinition->getCacheMaxAge(),
    ];

    $properties = $fieldDefinition->getPropertyDefinitions();
    if (count($properties) === 1) {
      // Flatten the structure for single-property fields.
      $derivative['type'] = reset($properties)->getDataType();
      $derivative['property'] = key($properties);
    }
    else {
      $derivative['type'] = StringHelper::camelCase('field', $entityTypeId, $fieldName);
    }

    if ($fieldDefinition->isMultiple()) {
      $derivative['type'] = "[{$derivative['type']}]";
    }

    return [
      "$entityTypeId-$fieldName" => $derivative + $basePluginDefinition,
    ];
  }

  /**
   * @param $entityTypeId
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $fieldDefinition
   * @return array
   */
  protected function getParentsForField($entityTypeId, FieldStorageDefinitionInterface $fieldDefinition) {
    if ($fieldDefinition->isBaseField()) {
      return [StringHelper::camelCase($entityTypeId)];
    }

    if ($fieldDefinition instanceof FieldStorageConfigInterface) {
      return array_values(array_map(function ($bundleId) use ($entityTypeId) {
        return StringHelper::camelCase($entityTypeId, $bundleId);
      }, $fieldDefinition->getBundles()));
    }

    return [];
  }
}
