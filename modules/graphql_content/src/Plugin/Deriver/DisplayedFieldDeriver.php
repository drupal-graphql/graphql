<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_content\ContentEntitySchemaConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derive GraphQL fields for all fields exposed in graphql display modes.
 */
class DisplayedFieldDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Entity display storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $displayStorage;

  /**
   * Bundle info provider.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleInfo;

  /**
   * The schema configuration service.
   *
   * @var \Drupal\graphql_content\ContentEntitySchemaConfig
   */
  protected $schemaConfig;

  /**
   * DisplayedFieldDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager instance.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundleInfo
   *   Bundle info provider.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   Entity field manager instance.
   * @param \Drupal\graphql_content\ContentEntitySchemaConfig $schemaConfig
   *   The schema config service.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    EntityTypeBundleInfoInterface $bundleInfo,
    EntityFieldManagerInterface $entityFieldManager,
    ContentEntitySchemaConfig $schemaConfig
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->bundleInfo = $bundleInfo;
    $this->displayStorage = $entityTypeManager->getStorage('entity_view_display');
    $this->schemaConfig = $schemaConfig;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_field.manager'),
      $container->get('graphql_content.schema_config')
    );
  }

  /**
   * Retrieve the GraphQL display for a certain content entity bundle.
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle name.
   * @param string $viewMode
   *   The desired view mode.
   *
   * @return \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   *   The view display object.
   */
  protected function getDisplay($entityType, $bundle, $viewMode) {
    /** @var EntityViewDisplayInterface $display */
    $display = $this->displayStorage->load(implode('.', [
      $entityType, $bundle, $viewMode,
    ]));
    if (!$display || !$display->status()) {
      $display = $this->displayStorage
        ->load(implode('.', [$entityType, $bundle, 'default']));
    }
    return $display instanceof EntityViewDisplayInterface ? $display : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $this->derivatives = [];
    $bundles = $this->bundleInfo->getAllBundleInfo();

    foreach ($this->entityTypeManager->getDefinitions() as $typeId => $type) {
      if (!($type instanceof ContentEntityTypeInterface) || !array_key_exists($typeId, $bundles)) {
        continue;
      }

      $storageDefinitions = $this->entityFieldManager->getFieldStorageDefinitions($typeId);
      foreach (array_keys($bundles[$typeId]) as $bundle) {
        if ($viewMode = $this->schemaConfig->getExposedViewMode($typeId, $bundle)) {
          if ($display = $this->getDisplay($typeId, $bundle, $viewMode)) {
            foreach ($display->getComponents() as $field => $component) {
              if (isset($component['type']) && $component['type'] === 'graphql_raw_value') {
                // Raw values formatter is obsolete.
                continue;
              }

              $storageDefinition = isset($storageDefinitions[$field]) ? $storageDefinitions[$field] : NULL;
              if (isset($storageDefinition) && $storageDefinition instanceof BaseFieldDefinition) {
                // Skip base fields. We generate them globally across all
                // bundles.
                continue;
              }

              $metadata = CacheableMetadata::createFromObject($display);
              if (isset($storageDefinition)) {
                $metadata->addCacheableDependency($storageDefinition);
              }

              $this->derivatives[$typeId . '-' . $bundle . '-' . $field] = [
                'name' => StringHelper::propCase($field),
                'parents' => [StringHelper::camelCase([$typeId, $bundle])],
                'entity_type' => $typeId,
                'bundle' => $bundle,
                'field' => $field,
                'virtual' => !isset($storageDefinition),
                'multi' => isset($storageDefinition) ? $storageDefinition->getCardinality() !== 1 : FALSE,
                'schema_cache_tags' => $metadata->getCacheTags(),
                'schema_cache_contexts' => $metadata->getCacheContexts(),
                'schema_cache_max_age' => $metadata->getCacheMaxAge(),
              ] + $basePluginDefinition;
            }
          }
        }
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
