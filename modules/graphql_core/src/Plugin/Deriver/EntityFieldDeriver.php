<?php

namespace Drupal\graphql_core\Plugin\Deriver;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\Deriver\EntityFieldDeriverBase;
use Drupal\graphql_core\Plugin\GraphQL\Fields\EntityField;
use Drupal\graphql_core\Plugin\GraphQL\Types\EntityFieldType;

/**
 * Deriver for RawValue fields.
 */
class EntityFieldDeriver extends EntityFieldDeriverBase {

  /**
   * Provide plugin definition values from config field storage.
   *
   * @param string $entityTypeId
   *   The host entity type.
   * @param string $bundleId
   *   The host entity bundle.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage
   *   Field storage definition object.
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
