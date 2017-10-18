<?php

namespace Drupal\graphql_core\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generate GraphQLField plugins for config fields.
 */
abstract class EntityFieldDeriverBase extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Provides plugin definition values from fields.
   *
   * @param string $entityTypeId
   *   The host entity type.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $fieldDefinition
   *   Field definition object.
   * @param array $basePluginDefinition
   *   Base definition array.
   */
  abstract protected function getDerivativeDefinitionsFromFieldDefinition($entityTypeId, FieldStorageDefinitionInterface $fieldDefinition, array $basePluginDefinition);

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityBundleInfo;

  /**
   * The base plugin id.
   *
   * @var string
   */
  protected $basePluginId;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info'),
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
   * @param string $basePluginId
   *   The base plugin id.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    EntityFieldManagerInterface $entityFieldManager,
    EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    $basePluginId
  ) {
    $this->basePluginId = $basePluginId;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->entityBundleInfo = $entityTypeBundleInfo;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $this->derivatives = [];

    foreach ($this->entityTypeManager->getDefinitions() as $entityTypeId => $entityType) {
      $interfaces = class_implements($entityType->getClass());
      if (!array_key_exists(FieldableEntityInterface::class, $interfaces)) {
        continue;
      }

      foreach ($this->entityFieldManager->getFieldStorageDefinitions($entityTypeId) as $fieldStorageDefinition) {
        $this->derivatives = array_merge($this->derivatives, $this->getDerivativeDefinitionsFromFieldDefinition($entityTypeId, $fieldStorageDefinition, $basePluginDefinition));
      }
    }

    return $this->derivatives;
  }

  /**
   * Tells if given field has just a single property.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $definition
   *   Field definition.
   *
   * @return bool
   */
  protected function isSinglePropertyField(FieldStorageDefinitionInterface $definition) {
    $properties = $definition->getPropertyDefinitions();
    return count($properties) === 1;
  }

}
