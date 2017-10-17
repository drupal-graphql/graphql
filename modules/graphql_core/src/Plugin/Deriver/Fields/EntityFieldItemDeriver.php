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
  protected function getDerivativesFromPropertyDefinitions($entityTypeId, FieldStorageDefinitionInterface $definition, array $basePluginDefinition, $bundleId = NULL) {
    $fieldName = $definition->getName();
    $dataType = EntityFieldType::getId($entityTypeId, $fieldName);

    $propertyDefinitions = $definition->getPropertyDefinitions();
    foreach ($propertyDefinitions as $property => $propertyDefinition) {
      if ($propertyDefinition->getDataType() == 'map') {
        // TODO Is it possible to get the keys of a map (eg. the options array for link field) here?
        continue;
      }

      $this->derivatives["$entityTypeId-$fieldName-$property"] = [
        'name' => StringHelper::propCase($property),
        'property' => $property,
        'multi' => FALSE,
        'type' => $this->typeMapper->typedDataToGraphQLFieldType($propertyDefinition),
        'types' => [$dataType],
        'schema_cache_tags' => array_merge($definition->getCacheTags(), ['entity_field_info']),
        'schema_cache_contexts' => $definition->getCacheContexts(),
        'schema_cache_max_age' => $definition->getCacheMaxAge(),
      ] + $basePluginDefinition;
    }
  }

}
