<?php

namespace Drupal\graphql_content_mutation;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Trait to read and interpret graphql_content_mutation configuration.
 */
class ContentEntityMutationSchemaConfig {

  protected $types;

  /**
   * ContentEntityMutationSchemaConfig constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $config = $configFactory->get('graphql_content_mutation.schema');
    $this->types = $config ? $config->get('types') : [];
    $this->types = $this->types ?: [];
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
  public function exposeCreate($entityType, $bundle) {
    return ((bool) NestedArray::getValue($this->types, [
      $entityType, 'bundles', $bundle, 'create',
    ]));
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
  public function exposeUpdate($entityType, $bundle) {
    return ((bool) NestedArray::getValue($this->types, [
      $entityType, 'bundles', $bundle, 'update',
    ]));
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
  public function exposeDelete($entityType) {
    return ((bool) NestedArray::getValue($this->types, [
      $entityType, 'delete',
    ]));
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
  public function exposeAnyCreateOrUpdate($entityType) {
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
  }

}
