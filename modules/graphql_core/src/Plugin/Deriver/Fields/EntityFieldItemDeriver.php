<?php

namespace Drupal\graphql_core\Plugin\Deriver\Fields;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\TypeMapper;
use Drupal\graphql_core\Plugin\GraphQL\Types\Entity\EntityFieldType;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityFieldItemDeriver extends EntityFieldDeriverBase {

  /**
   * The type mapper service.
   *
   * @var \Drupal\graphql_core\TypeMapper
   */
  protected $typeMapper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('graphql_core.type_mapper'),
      $basePluginId
    );
  }

  /**
   * RawValueFieldItemDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The bundle info service.
   * @param \Drupal\graphql_core\TypeMapper $typeMapper
   *   The graphql type mapper service.
   * @param string $basePluginId
   *   The base plugin id.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    EntityFieldManagerInterface $entityFieldManager,
    EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    TypeMapper $typeMapper,
    $basePluginId
  ) {
    parent::__construct($entityTypeManager, $entityFieldManager, $entityTypeBundleInfo, $basePluginId);
    $this->typeMapper = $typeMapper;
  }

  /**
   * {@inheritdoc}
   */
  protected function getBaseFieldDefinition($entityTypeId, BaseFieldDefinition $baseFieldDefinition, array $basePluginDefinition) {
    $this->getDerivativesFromPropertyDefinitions($entityTypeId, $baseFieldDefinition, $basePluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  protected function getConfigFieldDefinition($entityTypeId, $bundleId, FieldStorageDefinitionInterface $storage, array $basePluginDefinition) {
    $this->getDerivativesFromPropertyDefinitions($entityTypeId, $storage, $basePluginDefinition);
  }

  /**
   * Provide plugin definition values from base fields.
   *
   * @param string $entityTypeId
   *   The host entity type.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $definition
   *   Field definition object.
   * @param array $basePluginDefinition
   *   Base plugin definition array.
   */
  protected function getDerivativesFromPropertyDefinitions($entityTypeId, FieldStorageDefinitionInterface $definition, array $basePluginDefinition) {
    $fieldName = $definition->getName();
    $dataType = EntityFieldType::getId($entityTypeId, $fieldName);

    foreach ($definition->getPropertyDefinitions() as $property => $definition) {
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
