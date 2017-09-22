<?php

namespace Drupal\graphql_content_mutation;

use Drupal\graphql_content\ContentEntitySchemaConfig;

/**
 * Trait to read and interpret graphql_content_mutation configuration.
 */
class ContentEntityMutationSchemaConfig extends ContentEntitySchemaConfig {

  /**
   * Get the list of exposed entity mutations.
   *
   * @param string $entityType
   *   The entity type id.
   *
   * @return array
   *   List of exposed mutations.
   */
  public function getExposedEntityMutations($entityType) {
    if (!$this->isEntityTypeExposed($entityType)) {
      return [];
    }

    $mutations = $this->getConfig($entityType)->get('mutations');
    if (empty($mutations)) {
      // Make sure we always return an array.
      return [];
    }

    return $mutations;
  }

  /**
   * Get the list of exposed bundle mutations.
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle machine name.
   *
   * @return array
   *   List of exposed mutations.
   */
  public function getExposedEntityBundleMutations($entityType, $bundle) {
    if (!$this->isEntityBundleExposed($entityType, $bundle)) {
      return [];
    }

    $mutations = $this->getConfig($entityType, $bundle)->get('mutations');
    if (empty($mutations)) {
      // Make sure we always return an array.
      return [];
    }

    return $mutations;
  }

  /**
   * Expose entity mutations to graphQL schema.
   *
   * @param string $entityType
   *   The entity type id.
   * @param array $mutations
   *   The list of allowed mutations. Use empty array to unexpose mutations.
   */
  public function exposeEntityMutations($entityType, array $mutations) {
    $options = ['mutations' => $mutations];
    if (!empty($mutations)) {
      $options['exposed'] = TRUE;
    }
    $this->configureExposedEntity($entityType, $options);
  }

  /**
   * Expose entity bundle mutations to graphQL schema.
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle machine name.
   * @param array $mutations
   *   The list of allowed mutations. Use empty array to unexpose mutations.
   */
  public function exposeEntityBundleMutations($entityType, $bundle, array $mutations) {
    $options = ['mutations' => $mutations];
    if (!empty($mutations)) {
      $options['exposed'] = TRUE;
    }
    $this->configureExposedEntityBundle($entityType, $bundle, $options);
  }

  /**
   * Check if entity creation is exposed.
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle machine name.
   *
   * @return bool
   *   Boolean value indicating if entity creation is exposed.
   */
  public function isCreateExposed($entityType, $bundle) {
    $exposedMutations = $this->getExposedEntityBundleMutations($entityType, $bundle);
    return in_array('create', $exposedMutations);
  }

  /**
   * Check if entity update is exposed.
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $bundle
   *   The bundle machine name.
   *
   * @return bool
   *   Boolean value indicating if entity update is exposed.
   */
  public function isUpdateExposed($entityType, $bundle) {
    $exposedMutations = $this->getExposedEntityBundleMutations($entityType, $bundle);
    return in_array('update', $exposedMutations);
  }

  /**
   * Check if entity deletion is exposed.
   *
   * @param string $entityType
   *   The entity type id.
   *
   * @return bool
   *   Boolean value indicating if entity deletion is exposed.
   */
  public function isDeleteExposed($entityType) {
    $exposedMutations = $this->getExposedEntityMutations($entityType);
    return in_array('delete', $exposedMutations);
  }

  /**
   * Check if any bundle exposes create or update operations.
   *
   * @param string $entityType
   *   The entity type id.
   *
   * @return bool
   *   Boolean value indicating if any bundle exposes create or update.
   */
  public function isFieldMutationExposed($entityType) {
    // @todo
    return TRUE;
    /*
    $bundles = NestedArray::getValue($this->types, [
      $entityType, 'bundles',
    ]);

    if (!empty($bundles)) {
      foreach ($bundles as $bundle) {
        if (count(array_filter($bundle))) {
          return TRUE;
        }
      }
    }

    return FALSE;
    */
  }

}
