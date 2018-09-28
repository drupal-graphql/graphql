<?php

namespace Drupal\graphql_core\Plugin\Deriver\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\Deriver\EntityFieldDeriverBase;

class EntityFieldPropertyDeriver extends EntityFieldDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function getDerivativeDefinitionsFromFieldDefinition(FieldDefinitionInterface $fieldDefinition, array $basePluginDefinition) {
    $fieldType = $fieldDefinition->getType();
    $entityTypeId = $fieldDefinition->getTargetEntityTypeId();

    if (isset($basePluginDefinition['field_types']) && in_array($fieldType, $basePluginDefinition['field_types'])) {
      $fieldName = $fieldDefinition->getName();
      $fieldBundle = $fieldDefinition->getTargetBundle() ?: '';

      return ["$entityTypeId-$fieldBundle-$fieldName" => [
        'parents' => [StringHelper::camelCase('field', $entityTypeId, $fieldBundle, $fieldName)],
      ] + $basePluginDefinition];
    }

    return [];
  }
}