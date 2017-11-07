<?php

namespace Drupal\graphql_core\Plugin\Deriver\Fields;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\Deriver\EntityFieldDeriverWithTypeMapping;
use Drupal\graphql_core\Plugin\GraphQL\Fields\Entity\EntityField;
use Drupal\graphql_core\Plugin\GraphQL\Types\Entity\EntityFieldType;

// TODO Write tests for entity reference graph traversal.

// TODO Should we expose config entities?

// TODO Convert timestamps to strings?

/**
 * Deriver for RawValue fields.
 */
class EntityFieldDeriver extends EntityFieldDeriverWithTypeMapping {

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
      'name' => EntityField::getId($fieldName),
      'description' => $fieldDefinition->getDescription(),
      'multi' => $fieldDefinition->isMultiple(),
      'field' => $fieldName,
      'schema_cache_tags' => array_merge($fieldDefinition->getCacheTags(), ['entity_field_info']),
      'schema_cache_contexts' => $fieldDefinition->getCacheContexts(),
      'schema_cache_max_age' => $fieldDefinition->getCacheMaxAge(),
    ];

    $properties = $fieldDefinition->getPropertyDefinitions();
    if (count($properties) === 1) {
      // Flatten the structure for single-property fields.
      /** @var \Drupal\Core\TypedData\DataDefinitionInterface $property */
      $property = reset($properties);
      $keys = array_keys($properties);

      $derivative['type'] = $this->typeMapper->typedDataToGraphQLFieldType($property);
      $derivative['property'] = reset($keys);
    }
    else {
      $derivative['type'] = EntityFieldType::getId($entityTypeId, $fieldName);
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
