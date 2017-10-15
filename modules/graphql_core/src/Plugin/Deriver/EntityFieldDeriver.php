<?php

namespace Drupal\graphql_core\Plugin\Deriver;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\GraphQL\Fields\EntityField;
use Drupal\graphql_core\Plugin\GraphQL\Types\EntityFieldType;

// TODO Reduce single-property fields to scalars.

// TODO Convert timestamps to strings?

/**
 * Deriver for RawValue fields.
 */
class EntityFieldDeriver extends EntityFieldDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function getBaseFieldDefinition($entityTypeId, BaseFieldDefinition $baseFieldDefinition, array $basePluginDefinition) {
    $fieldName = $baseFieldDefinition->getName();

    $this->derivatives["$entityTypeId-$fieldName"] = [
      'types' => [StringHelper::camelCase([$entityTypeId])],
      'name' => EntityField::getId($fieldName),
      'multi' => $baseFieldDefinition->isMultiple(),
      'field' => $fieldName,
      'type' => EntityFieldType::getId($entityTypeId, $fieldName),
    ] + $basePluginDefinition;
  }

  /**
   * {@inheritdoc}
   */
  protected function getConfigFieldDefinition($entityTypeId, $bundleId, FieldStorageDefinitionInterface $storage, array $basePluginDefinition) {
    $fieldName = $storage->getName();

    $this->derivatives["$entityTypeId-$bundleId-$fieldName"] = [
      'types' => [StringHelper::camelCase([$entityTypeId, $bundleId])],
      'name' => EntityField::getId($fieldName),
      'multi' => $storage ? $storage->getCardinality() != 1 : FALSE,
      'field' => $fieldName,
      'type' => EntityFieldType::getId($entityTypeId, $fieldName),
    ] + $basePluginDefinition;
  }

}
