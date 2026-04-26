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
   *
   * @param bool|string $pluginSubdirectory
   *   The namespace-relative path to the plugin sub-directory.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $definitionCacheBackend
   *   The cache backend to use to load plugin information.
   * @param string|null $pluginInterface
   *   (optional) The interface each plugin should implement.
   * @param string $pluginAttributeName
   *   The name of the provider attribute to search for in plugin definitions.
   * @param string $pluginAnnotationName
   *   The name of the annotation to search for in plugin definitions.
   * @param array $config
   *   The configuration service parameters.
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
