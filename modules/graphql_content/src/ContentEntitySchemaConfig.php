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
  protected function getConfigName($entityType, $bundle = '') {
    if (empty($bundle)) {
      return 'graphql.exposed_entity.' . $entityType;
    }
    return 'graphql.exposed_bundle.' . $entityType . '.' . $bundle;
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
  protected function getConfig($entityType, $bundle = '') {
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
  protected function getEditableConfig($entityType, $bundle = '') {
    $config_name = $this->getConfigName($entityType, $bundle);
    return $this->configFactory->getEditable($config_name);
  }

  /**
   * Configure exposed entity.
   *
   * @param string $entityType
   *   The entity type id.
   * @param array $options
   *   An associative array of configuration options. It may contain the following elements.
   *   - 'exposed': Boolean value indicating if the entity should be exposed or hidden.
   *   - 'mutations': The list of allowed mutations.
   */
  protected function configureExposedEntity($entityType, array $options) {
    $config = $this->getEditableConfig($entityType);
    foreach ($options as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();
  }

  /**
   * Configure exposed entity bundle.
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle machine name.
   * @param array $options
   *   An associative array of configuration options. It may contain the following elements.
   *   - 'exposed': Boolean value indicating if the bundle should be exposed or hidden.
   *   - '$view_mode': The view_mode machine name. (together with entity type, like "node.graphql")
   *      Use '__none__' in case the fields should not be exposed.
   *   - 'mutations': The list of allowed mutations.
   */
  protected function configureExposedEntityBundle($entityType, $bundle, array $options) {
    // Expose entity if not yet exposed.
    $exposed = !empty($options['exposed']);
    if ($exposed && !$this->isEntityTypeExposed($entityType)) {
      $this->exposeEntity($entityType);
    }
    $config = $this->getEditableConfig($entityType, $bundle);
    foreach ($options as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();
  }

  /**
   * Expose entity to graphQL schema.
   *
   * @param string $entityType
   *   The entity type id.
   */
  public function exposeEntity($entityType) {
    $this->configureExposedEntity($entityType, ['exposed' => TRUE]);
  }

  /**
   * Unexpose (remove) entity from graphQL schema.
   *
   * @param string $entityType
   *   The entity type id.
   */
  public function unexposeEntity($entityType) {
    $this->configureExposedEntity($entityType, ['exposed' => FALSE]);
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
    $options = ['exposed' => TRUE, 'view_mode' => $view_mode];
    $this->configureExposedEntityBundle($entityType, $bundle, $options);
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
    $options = ['exposed' => FALSE, 'view_mode' => '__none__'];
    $this->configureExposedEntityBundle($entityType, $bundle, $options);
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
