<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\graphql_content\ContentEntitySchemaConfig;
use Drupal\graphql_content\Plugin\GraphQL\Types\RawValueFieldType;
use Drupal\graphql_content\TypeMapper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RawValueFieldItemDeriver extends FieldFormatterDeriver {

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
   *   An entity type manager instance.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   An entity field manager instance.
   * @param \Drupal\graphql_content\ContentEntitySchemaConfig $config
   *   A schema configuration service.
   * @param \Drupal\graphql_content\TypeMapper $typeMapper
   *   The graphql type mapper service.
   * @param string $basePluginId
   *   The base plugin id.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    EntityFieldManagerInterface $entityFieldManager,
    ContentEntitySchemaConfig $config,
    TypeMapper $typeMapper,
    $basePluginId
  ) {
    parent::__construct($entityTypeManager, $entityFieldManager, $config, $basePluginId);
    $this->typeMapper = $typeMapper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('graphql_content.schema_config'),
      $container->get('graphql_content.type_mapper'),
      $basePluginId
    );
  }

  protected function getDefinitions($entityType, $bundle, array $displayOptions, FieldStorageDefinitionInterface $storage = NULL) {
    $fieldName = $storage->getName();
    $dataType = RawValueFieldType::getId($entityType, $fieldName);

    // Add the subfields, eg. value, summary.
    $definitions = [];
    foreach ($storage->getSchema()['columns'] as $columnName => $schema) {
      $definitions["$entityType-$fieldName-$columnName"] = [
        'name' => graphql_core_propcase($columnName),
        'schema_column' => $columnName,
        'multi' => FALSE,
        'type' => $this->typeMapper->typedDataToGraphQLFieldType($schema['type']),
        'types' => [$dataType],
      ];
    }

    return $definitions;
  }
}
