<?php

namespace Drupal\graphql_core\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generate GraphQLField plugins for config fields.
 */
abstract class EntityFieldDeriverBase extends DeriverBase implements ContainerDeriverInterface {
  use DependencySerializationTrait;

  /**
   * Provides plugin definition values from fields.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition
   *   Field definition object.
   * @param array $basePluginDefinition
   *   Base definition array.
   *
   * @return array
   *   The derived plugin definitions for the given field.
   */
  abstract protected function getDerivativeDefinitionsFromFieldDefinition(FieldDefinitionInterface $fieldDefinition, array $basePluginDefinition);

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
    foreach ($this->entityTypeManager->getDefinitions() as $entityTypeId => $entityType) {
      if (!$entityType->entityClassImplements(FieldableEntityInterface::class)) {
        continue;
      }

      foreach ($this->entityFieldManager->getBaseFieldDefinitions($entityTypeId) as $fieldDefinition) {
        if ($derivatives = $this->getDerivativeDefinitionsFromFieldDefinition($fieldDefinition, $basePluginDefinition)) {
          $this->derivatives = array_merge($this->derivatives, $derivatives);
        }
      }

      foreach ($this->entityBundleInfo->getBundleInfo($entityTypeId) as $bundleId => $bundleInfo) {
        foreach ($this->entityFieldManager->getFieldDefinitions($entityTypeId, $bundleId) as $fieldDefinition) {
          if ($derivatives = $this->getDerivativeDefinitionsFromFieldDefinition($fieldDefinition, $basePluginDefinition)) {
            $this->derivatives = array_merge($this->derivatives, $derivatives);
          }
        }
      }
    }

    return $this->derivatives;
  }

}
