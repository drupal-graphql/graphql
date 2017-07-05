<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\graphql_content\Plugin\GraphQL\Types\RawValueFieldType;
use Drupal\graphql_content\TypeMapper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RawValueFieldItemDeriver extends FieldDeriverBase {

  /**
   * The type mapper service.
   *
   * @var \Drupal\graphql_content\TypeMapper
   */
  protected $typeMapper;

  /**
   * RawValueFieldItemDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundleInfo
   *   The entity type bundle info service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager service.
   * @param \Drupal\graphql_content\TypeMapper $typeMapper
   *   The graphql type mapper service.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    EntityTypeBundleInfoInterface $bundleInfo,
    EntityFieldManagerInterface $entityFieldManager,
    TypeMapper $typeMapper
  ) {
    parent::__construct($entityTypeManager, $bundleInfo, $entityFieldManager);
    $this->typeMapper = $typeMapper;
  }

  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_field.manager'),
      $container->get('graphql.type_mapper')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function isFieldSupported($displaySettings) {
    return isset($displaySettings['type'])
      && $displaySettings['type'] === 'raw_value';
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldPluginDefinition($basePluginDefinition, EntityTypeInterface $type, $bundle, $fieldName, $storage) {
    /** @var \Drupal\field\Entity\FieldStorageConfig $storage */

    // Base fields are not handled at the moment.
    if ($storage) {
      // Object type in GraphQL, eg. NodeBodyRawValue.
      $dataType = RawValueFieldType::getId($type->id(), $fieldName);

      // Add the subfields, eg. value, summary.
      foreach ($storage->getSchema()['columns'] as $columnName => $schema) {
        $this->derivatives["{$type->id()}-$fieldName-$columnName"] = [
          'name' => graphql_core_propcase($columnName),
          'schema_column' => $columnName,
          'multi' => FALSE,
          'type' => $this->typeMapper->typedDataToGraphQLFieldType($schema['type']),
          'types' => [$dataType],
        ] + $basePluginDefinition;
      }
    }
  }

}
