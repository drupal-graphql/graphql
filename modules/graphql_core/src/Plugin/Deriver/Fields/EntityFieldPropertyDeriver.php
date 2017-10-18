<?php

namespace Drupal\graphql_core\Plugin\Deriver\Fields;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\graphql_core\Plugin\Deriver\EntityFieldDeriverWithTypeMapping;
use Drupal\graphql_core\Plugin\GraphQL\Types\Entity\EntityFieldType;

/**
 * Attach new properties to field types.
 */
class EntityFieldPropertyDeriver extends EntityFieldDeriverWithTypeMapping {

  protected function getDerivativeDefinitionsFromFieldDefinition(
    $entityTypeId,
    FieldStorageDefinitionInterface $fieldDefinition,
    array $basePluginDefinition
  ) {

    $derivatives = [];

    if (isset($basePluginDefinition['field_types']) && in_array($fieldDefinition->getType(), $basePluginDefinition['field_types'])) {
      $fieldName = $fieldDefinition->getName();
      $derivatives["$entityTypeId-$fieldName-" . $basePluginDefinition['id']] = [
        'parents' => [EntityFieldType::getId($entityTypeId, $fieldName)]
      ] + $basePluginDefinition;
    }

    return $derivatives;
  }

}