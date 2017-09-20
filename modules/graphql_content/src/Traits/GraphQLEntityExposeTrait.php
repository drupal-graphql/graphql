<?php

namespace Drupal\qraphql_content\Traits;

/**
 * Provides methods to expose entities and entity bundles.
 */
trait GraphQLEntityExposeTrait {

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
   * Configure exposed entity.
   *
   * @param string $entityType
   *   The entity type id.
   * @param bool $exposed
   *   Boolean value indicating if the entity should be exposed or hidden.
   */
  private function configureExposedEntity($entityType, bool $exposed) {
    $config_name = $this->getConfigName($entityType);
    $config = \Drupal::configFactory()->getEditable($config_name);
    $config->set('exposed', $exposed)->save();
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
    $config_name = $this->getConfigName($entityType, $bundle);
    $config = \Drupal::configFactory()->getEditable($config_name);

    $config
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
  protected function exposeEntity($entityType) {
    $this->configureExposedEntity($entityType, TRUE);
  }

  /**
   * Unexpose (remove) entity from graphQL schema.
   *
   * @param string $entityType
   *   The entity type id.
   */
  protected function unexposeEntity($entityType) {
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
  protected function exposeEntityBundle($entityType, $bundle, $view_mode = '__none__') {
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
  protected function unexposeEntityBundle($entityType, $bundle) {
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
  protected function isEntityTypeExposed($entityType) {
    $config_name = $this->getConfigName($entityType);
    return (bool) \Drupal::config($config_name)->get('exposed');
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
  protected function isEntityBundleExposed($entityType, $bundle) {
    $config_name = $this->getConfigName($entityType, $bundle);
    $exposed = (bool) \Drupal::config($config_name)->get('exposed');
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
  protected function getExposedViewMode($entityType, $bundle) {
    if (!$this->isEntityBundleExposed($entityType, $bundle)) {
      return NULL;
    }

    $config_name = $this->getConfigName($entityType, $bundle);
    $viewMode = \Drupal::config($config_name)->get('view_mode');

    if (empty($viewMode) || $viewMode == '__none__') {
      return NULL;
    }

    // Remove the entity type from view mode name.
    return explode('.', $viewMode, 2)[1];
  }
}
