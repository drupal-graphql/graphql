<?php

namespace Drupal\graphql_core\Plugin\Deriver\Types;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\graphql_core\Plugin\Deriver\Fields\EntityFieldDeriverBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\graphql_core\Plugin\GraphQL\Interfaces\Entity\EntityType;
use Drupal\graphql_core\Plugin\GraphQL\Types\Entity\EntityFieldType;
use Drupal\graphql_core\Plugin\GraphQL\Types\Entity\EntityBundle;

/**
 * Derive GraphQL types for raw values of drupal fields.
 */
class EntityFieldTypeDeriver extends EntityFieldDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function getBaseFieldDefinition($entityTypeId, BaseFieldDefinition $baseFieldDefinition, array $basePluginDefinition) {
    $fieldName = $baseFieldDefinition->getName();

    $this->derivatives["$entityTypeId-$fieldName"] = [
      'name' => EntityFieldType::getId($entityTypeId, $fieldName),
      'entity_type' => $entityTypeId,
      'data_type' => "entity:$entityTypeId:$fieldName",
      'interfaces' => [EntityType::getId($entityTypeId)],
    ] + $basePluginDefinition;
  }

  /**
   * {@inheritdoc}
   */
  protected function getConfigFieldDefinition($entityTypeId, $bundleId, FieldStorageDefinitionInterface $storage, array $basePluginDefinition) {
    $fieldName = $storage->getName();

    $this->derivatives["$entityTypeId-$bundleId-$fieldName"] = [
      'name' => EntityFieldType::getId($entityTypeId, $fieldName),
      'entity_type' => $entityTypeId,
      'data_type' => "entity:$entityTypeId:$bundleId:$fieldName",
      'interfaces' => [EntityBundle::getId($entityTypeId, $bundleId)],
      'bundle' => $bundleId,
    ] + $basePluginDefinition;
  }

}
