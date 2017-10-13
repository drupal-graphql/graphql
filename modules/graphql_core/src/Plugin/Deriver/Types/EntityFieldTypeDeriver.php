<?php

namespace Drupal\graphql_core\Plugin\Deriver\Types;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\graphql_core\Plugin\Deriver\Fields;
use Drupal\graphql_core\Plugin\Deriver\Fields\EntityFieldDeriverBase;
use Drupal\graphql_core\Plugin\GraphQL\Types\Entity\EntityBundle;
use Drupal\graphql_core\Plugin\GraphQL\Types\Entity\EntityFieldType;

/**
 * Derive GraphQL types for raw values of drupal fields.
 */
class EntityFieldTypeDeriver extends Fields\EntityFieldDeriverBase {

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
