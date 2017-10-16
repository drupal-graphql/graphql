<?php

namespace Drupal\graphql_core\Plugin\Deriver;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\graphql_core\Plugin\Deriver\Fields\EntityFieldDeriverBase;
use Drupal\graphql_core\TypeMapper;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class EntityFieldDeriverWithTypeMapping extends EntityFieldDeriverBase {

  /**
   * Provide plugin definition values from both base and config fields.
   *
   * @param string $entityTypeId
   *   The host entity type.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $definition
   *   Field definition object.
   * @param array $basePluginDefinition
   *   Base plugin definition array.
   * @param null|string $bundleId
   *   Bundle id.
   */
  abstract protected function getDerivativesFromPropertyDefinitions($entityTypeId, FieldStorageDefinitionInterface $definition, array $basePluginDefinition, $bundleId = NULL);

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
   * EntityFieldDeriverBase constructor.
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
    $this->getDerivativesFromPropertyDefinitions($entityTypeId, $storage, $basePluginDefinition, $bundleId);
  }

}
