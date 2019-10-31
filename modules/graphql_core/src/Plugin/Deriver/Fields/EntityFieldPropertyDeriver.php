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

    if (isset($basePluginDefinition['field_types']) && in_array($fieldType, $basePluginDefinition['field_types'])) {
      $fieldName = $fieldDefinition->getName();
      $fieldBundle = $fieldDefinition->getTargetBundle() ?: '';
      $entityTypeId = $fieldDefinition->getTargetEntityTypeId();
      $entityType = $this->entityTypeManager->getDefinition($entityTypeId);
      $supportsBundles = $entityType->hasKey('bundle');

      if (!isset($basePluginDefinition['parents'])) {
        $basePluginDefinition['parents'] = [];
      }

      $parents = [StringHelper::camelCase('field', $entityTypeId, $supportsBundles ? $fieldBundle : '', $fieldName)];
      return ["$entityTypeId-$fieldBundle-$fieldName" => [
        'parents' => array_merge($parents, $basePluginDefinition['parents']),
      ] + $basePluginDefinition];
    }

    return [];
  }
}
