<?php

namespace Drupal\graphql_content;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Trait to read and interpret graphql_content configuration.
 */
class ContentEntitySchemaConfig {

  protected $types;

  /**
   * ContentEntitySchemaConfig constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $config = $configFactory->get('graphql_content.schema');
    $this->types = $config ? $config->get('types') : [];
    $this->types = $this->types ?: [];
  }

  /**
   * Check if an entity type is exposed.
   *
   * @param string $entityType
   *   The entity type id.
   *
   * @return bool
   *   Boolean value indicating if the entity type is exposed.
   */
  public function isEntityTypeExposed($entityType) {
    return (bool) NestedArray::getValue($this->types, [
      $entityType, 'exposed',
    ]);
  }

  /**
   * Check if an entity bundle is exposed.
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle machine name.
   *
   * @return bool
   *   Boolean value indicating if the entity bundle is exposed.
   */
  public function isEntityBundleExposed($entityType, $bundle) {
    return ((bool) NestedArray::getValue($this->types, [
      $entityType, 'bundles', $bundle, 'exposed',
    ])) && $this->isEntityTypeExposed($entityType);
  }

  /**
   * Get the exposed view mode for an entity bundle.
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle machine name.
   *
   * @return string|null
   *   The view mode machine name, or NULL if no fields are exposed.
   */
  public function getExposedViewMode($entityType, $bundle) {
    if (!$this->isEntityBundleExposed($entityType, $bundle)) {
      return NULL;
    }

    $viewMode = NestedArray::getValue($this->types, [
      $entityType, 'bundles', $bundle, 'view_mode',
    ]);

    if (!$viewMode || $viewMode == '__none__') {
      return NULL;
    }

    list($type, $mode) = explode('.', $viewMode);

    return $mode;

  }

}
