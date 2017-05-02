<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
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
   * DisplayedFieldDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager instance.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundleInfo
   *   Bundle info provider.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   Entity field manager instance.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    EntityTypeBundleInfoInterface $bundleInfo,
    EntityFieldManagerInterface $entityFieldManager
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->bundleInfo = $bundleInfo;
    $this->displayStorage = $entityTypeManager->getStorage('entity_view_display');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * Retrieve the GraphQL display for a certain content entity bundle.
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle name.
   *
   * @return \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   *   The view display object.
   */
  protected function getDisplay($entityType, $bundle) {
    /** @var EntityViewDisplayInterface $display */
    $display = $this->displayStorage
      ->load(implode('.', [$entityType, $bundle, 'graphql']));
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
      if (!($type instanceof ContentEntityTypeInterface)) {
        continue;
      }

      $storages = $this->entityFieldManager->getFieldStorageDefinitions($typeId);

      foreach (array_keys($bundles[$typeId]) as $bundle) {
        if ($display = $this->getDisplay($typeId, $bundle)) {
          foreach (array_keys($display->getComponents()) as $field) {
            $this->derivatives[$typeId . '-' . $bundle . '-' . $field] = [
              'name' => graphql_core_propcase($field),
              'types' => [graphql_core_camelcase([$typeId, $bundle])],
              'entity_type' => $typeId,
              'bundle' => $bundle,
              'field' => $field,
              'virtual' => !array_key_exists($field, $storages),
              'multi' => array_key_exists($field, $storages) ? $storages[$field]->getCardinality() != 1 : FALSE,
              'cache_tags' => $display->getCacheTags(),
              'cache_contexts' => $display->getCacheContexts(),
              'cache_max_age' => $display->getCacheMaxAge(),
            ] + $basePluginDefinition;
          }
        }
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
