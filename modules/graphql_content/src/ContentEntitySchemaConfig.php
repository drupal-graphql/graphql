<?php

namespace Drupal\graphql_content;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides methods to expose entities and entity bundles to graphQL schema.
 */
class ContentEntitySchemaConfig {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ContentEntitySchemaConfig constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Get the configuration object name.
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle machine name.
   *
   * @return string
   *   The name of the configuration object storing the information about expose.
   *
   */
  private function getConfigName($entityType, $bundle = '') {
    if (empty($bundle)) {
      return 'graphql.exposed.' . $entityType;
    }
    return 'graphql.exposed.' . $entityType . '.' . $bundle;
  }

  /**
   * Get the immutable configuration object.
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle machine name.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The configuration object.
   *
   */
  private function getConfig($entityType, $bundle = '') {
    $config_name = $this->getConfigName($entityType, $bundle);
    return $this->configFactory->get($config_name);
  }

  /**
   * Get the mutable configuration object.
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle machine name.
   *
   * @return \Drupal\Core\Config\Config
   *   The configuration object.
   *
   */
  private function getEditableConfig($entityType, $bundle = '') {
    $config_name = $this->getConfigName($entityType, $bundle);
    return $this->configFactory->getEditable($config_name);
  }

  /**
   * Configure exposed entity.
   *
   * @param string $entityType
   *   The entity type id.
   * @param bool $exposed
   *   Boolean value indicating if the entity should be exposed or hidden.
   */
  private function configureExposedEntity($entityType, bool $exposed) {
    $this->getEditableConfig($entityType)
      ->set('exposed', $exposed)
      ->save();
  }

  /**
   * Configure exposed entity bundle.
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle machine name.
   * @param bool $exposed
   *   Boolean value indicating if the bundle should be exposed or hidden.
   * @param string $view_mode
   *   The view_mode machine name. (together with entity type, like "node.graphql")
   *   Use '__none__' in case the fields should not be exposed.
   */
  private function configureExposedEntityBundle($entityType, $bundle, bool $exposed, $view_mode = '__none__') {
    // Expose entity if not yet exposed.
    if ($exposed && !$this->isEntityTypeExposed($entityType)) {
      $this->exposeEntity($entityType);
    }
    $this->getEditableConfig($entityType, $bundle)
      ->set('exposed', $exposed)
      ->set('view_mode', $view_mode)
      ->save();
  }

  /**
   * Expose entity to graphQL schema.
   *
   * @param string $entityType
   *   The entity type id.
   */
  public function exposeEntity($entityType) {
    $this->configureExposedEntity($entityType, TRUE);
  }

  /**
   * Unexpose (remove) entity from graphQL schema.
   *
   * @param string $entityType
   *   The entity type id.
   */
  public function unexposeEntity($entityType) {
    $this->configureExposedEntity($entityType, FALSE);
  }

  /**
   * Expose entity bundle to graphQL schema.
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle machine name.
   * @param string $view_mode
   *   The view_mode machine name. (together with entity type, like node.graphql)
   *   Use '__none__' in case the fields should not be exposed.
   */
  public function exposeEntityBundle($entityType, $bundle, $view_mode = '__none__') {
    $this->configureExposedEntityBundle($entityType, $bundle, TRUE, $view_mode);
  }

  /**
   * Unexpose (remove) entity bundle from graphQL schema.
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle machine name.
   */
  public function unexposeEntityBundle($entityType, $bundle) {
    $this->configureExposedEntityBundle($entityType, $bundle, FALSE, '__none__');
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
    return (bool) $this->getConfig($entityType)->get('exposed');
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
    $exposed = (bool) $this->getConfig($entityType, $bundle)->get('exposed');
    return $exposed && $this->isEntityTypeExposed($entityType);
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

    $viewMode = $this->getConfig($entityType, $bundle)->get('view_mode');

    if (empty($viewMode) || $viewMode == '__none__') {
      return NULL;
    }

    // Remove the entity type from view mode name.
    return explode('.', $viewMode, 2)[1];
  }
}
