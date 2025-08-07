<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\graphql\Entity\ServerInterface;

/**
 * Manager that collects and exposes GraphQL schema plugins.
 *
 * @package Drupal\graphql\Plugin
 *
 * @codeCoverageIgnore
 */
class SchemaPluginManager extends DefaultPluginManager {

  /**
   * SchemaPluginManager constructor.
   *
   * @param bool|string $pluginSubdirectory
   *   The plugin's subdirectory.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend.
   * @param string|null $pluginInterface
   *   The interface each plugin should implement.
   * @param string $pluginAttributeName
   *   The name of the attribute that contains the plugin definition.
   * @param string $pluginAnnotationName
   *   The name of the annotation that contains the plugin definition.
   * @param array $config
   *   The configuration service parameter.
   */
  public function __construct(
    bool|string $pluginSubdirectory,
    \Traversable $namespaces,
    ModuleHandlerInterface $moduleHandler,
    CacheBackendInterface $cacheBackend,
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

    $this->alterInfo('graphql_schema');
    $this->useCaches(empty($config['development']));
    $this->setCacheBackend($cacheBackend, 'graphql_schema', ['graphql_schema']);
  }

  /**
   * Helper function to initialize a schema plugin with server configuration.
   */
  public function getInstanceFromServer(ServerInterface $server): SchemaPluginInterface {
    $schema_name = $server->get('schema');
    $schema_config = $server->get('schema_configuration') ?? [];
    $config = $schema_config[$schema_name] ?? [];
    $config['server_id'] = $server->id();
    return $this->createInstance($schema_name, $config);
  }

}
