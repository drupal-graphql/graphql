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

    $definitions = $definition->getPropertyDefinitions();

    foreach ($definitions as $property => $definition) {
      if ($definition->getDataType() == 'map') {
        // TODO Is it possible to get the keys of a map (eg. the options array for link field) here?
        continue;
      }

      $this->derivatives["$entityTypeId-$fieldName-$property"] = [
        'name' => StringHelper::propCase($property),
        'property' => $property,
        'multi' => FALSE,
        'type' => $this->typeMapper->typedDataToGraphQLFieldType($definition),
        'types' => [$dataType],
      ] + $basePluginDefinition;
    }
  }

}
