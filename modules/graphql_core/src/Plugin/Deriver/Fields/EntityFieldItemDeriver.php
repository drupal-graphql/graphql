<?php

namespace Drupal\graphql_core\Plugin\Deriver\Fields;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\Deriver\EntityFieldDeriverWithTypeMapping;
use Drupal\graphql_core\Plugin\GraphQL\Types\Entity\EntityFieldType;

class EntityFieldItemDeriver extends EntityFieldDeriverWithTypeMapping {

  /**
   * {@inheritdoc}
   */
  protected function getDerivativeDefinitionsFromFieldDefinition($entityTypeId, FieldStorageDefinitionInterface $fieldDefinition, array $basePluginDefinition, $bundleId = NULL) {
    $derivatives = [];
    $fieldName = $fieldDefinition->getName();
    $commonDefinition = [
      'parents' => [EntityFieldType::getId($entityTypeId, $fieldName)],
      'schema_cache_tags' => array_merge($fieldDefinition->getCacheTags(), ['entity_field_info']),
      'schema_cache_contexts' => $fieldDefinition->getCacheContexts(),
      'schema_cache_max_age' => $fieldDefinition->getCacheMaxAge(),
    ];

    foreach ($fieldDefinition->getPropertyDefinitions() as $property => $propertyDefinition) {
      if (($type = $this->typeMapper->getTypeFromDataDefinition($propertyDefinition)) === NULL) {
        if ($propertyDefinition->getDataType() === 'map') {
          continue;
        }

        // Default to 'String' for unknown types.
        $type = 'String';
      }

      $derivatives["$entityTypeId-$fieldName-$property"] = [
        'name' => StringHelper::propCase($property),
        'description' => $propertyDefinition->getDescription(),
        'property' => $property,
        'multi' => FALSE,
        'type' => $type,
      ] + $commonDefinition + $basePluginDefinition;
    }

    return $derivatives;
  }

}
