<?php

namespace Drupal\graphql\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;

/**
 * Collects plugins that can add to or change the GraphQL resolver registry.
 */
class ResolverMapPluginManager extends DefaultPluginManager {

  /**
   * ResolverMapPluginManager constructor.
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
   * @param string $pluginAnnotationName
   *   The name of the annotation that contains the plugin definition.
   * @param array $config
   *   The configuration service parameter.
   */
  public function __construct(
    $pluginSubdirectory,
    \Traversable $namespaces,
    ModuleHandlerInterface $moduleHandler,
    CacheBackendInterface $cacheBackend,
    $pluginInterface,
    $pluginAnnotationName,
    array $config
  ) {
    parent::__construct(
      $pluginSubdirectory,
      $namespaces,
      $moduleHandler,
      $pluginInterface,
      $pluginAnnotationName
    );

    $this->useCaches(empty($config['development']));
    $this->setCacheBackend($cacheBackend, 'resolver_map');
  }

  /**
   * Register resolvers for given schema.
   *
   * @param string $schema
   *   GraphQL schema to get resolvers for.
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   *   Optional. Resolver registry containing custom default settings.
   *
   * @return \Drupal\graphql\GraphQL\ResolverRegistry
   *   Registry with registered field/type resolvers.
   */
  public function registerResolvers($schema, ResolverRegistry $registry = NULL) {
    if (!$registry) {
      $registry = new ResolverRegistry([]);
    }
    $builder = new ResolverBuilder();
    foreach ($this->getDefinitions() as $plugin_id => $definition) {
      if ($schema == $definition['schema']) {
        /** @var \Drupal\graphql\Plugin\ResolverMapPluginInterface $plugin */
        $plugin = $this->createInstance($plugin_id, $definition);
        $plugin->registerResolvers($registry, $builder);
      }
    }
    return $registry;
  }

}
