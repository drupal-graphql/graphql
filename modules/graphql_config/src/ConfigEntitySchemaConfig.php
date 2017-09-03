<?php

namespace Drupal\graphql_config;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Trait to read and interpret graphql_config configuration.
 */
class ConfigEntitySchemaConfig {

  protected $types;

  /**
   * ConfigEntitySchemaConfig constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $config = $configFactory->get('graphql_config.schema');
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

}
