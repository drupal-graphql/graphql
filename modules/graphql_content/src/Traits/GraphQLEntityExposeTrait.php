<?php

namespace Drupal\qraphql_content\Traits;

/**
 * Provides methods to expose entities and entity bundles.
 *
 * This trait is meant to be used only by test classes.
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
   * Expose entity to graphQL schema.
   *
   * @param bool $exposed
   *   Boolean value indicating if the entity should be exposed or hidden.
   * @param string $entityType
   *   The entity type id.
   */
  protected function exposeEntity(bool $exposed, $entityType) {
    $config_name = $this->getConfigName($entityType);
    $config = \Drupal::configFactory()->getEditable($config_name);
    $config->set('exposed', $exposed)->save();
  }
  /**
   * Expose entity bundle to graphQL schema.
   *
   * @param bool $exposed
   *   Boolean value indicating if the bundle should be exposed or hidden.
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle machine name.
   * @param string $view_mode
   *   The view_mode machine name. (together with entity type, like node.graphql)
   *   Use '__none__' in case the fields should not be exposed.
   */
  protected function exposeEntityBundle(bool $exposed, $entityType, $bundle, $view_mode = '__none__') {
    // Expose entity if not yet exposed.
    if ($exposed && !$this->isEntityTypeExposed($entityType)) {
      $this->exposeEntity(TRUE, $entityType);
    }
    $config_name = $this->getConfigName($entityType, $bundle);
    $config = \Drupal::configFactory()->getEditable($config_name);

    $config
      ->set('exposed', $exposed)
      ->set('view_mode', $view_mode)
      ->save();
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
