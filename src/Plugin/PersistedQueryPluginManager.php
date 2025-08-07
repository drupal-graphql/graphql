<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Collects persisted queries that are defined as plugins.
 */
class PersistedQueryPluginManager extends DefaultPluginManager {

  /**
   * PersistedQueryPluginManager constructor.
   */
  public function __construct(
    bool|string $pluginSubdirectory,
    \Traversable $namespaces,
    ModuleHandlerInterface $moduleHandler,
    CacheBackendInterface $definitionCacheBackend,
    ?string $pluginInterface,
    string $pluginAttributeName,
    string $pluginAnnotationName,
    array $config,
  ) {
    parent::__construct(
      $pluginSubdirectory,
      $namespaces,
      $moduleHandler,
      $pluginInterface,
      $pluginAttributeName,
      $pluginAnnotationName
    );

    $this->alterInfo('graphql_persisted_query');
    $this->useCaches(empty($config['development']));
    $this->setCacheBackend($definitionCacheBackend, 'graphql_persisted_query', ['graphql_persisted_query']);
  }

}
