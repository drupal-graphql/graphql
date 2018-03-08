<?php

namespace Drupal\graphql\Plugin;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

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

    $this->alterInfo('graphql_schema');
    $this->useCaches(empty($config['development']));
    $this->setCacheBackend($cacheBackend, 'schemas', ['graphql']);
  }

  /**
   * {@inheritdoc}
   */
  protected function setCachedDefinitions($definitions) {
    $this->definitions = $definitions;
    $this->cacheSet($this->cacheKey, $definitions, $this->getCacheMaxAge(), $this->getCacheTags());
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $definitions = $this->getDefinitions();
    return array_reduce($definitions, function ($carry, $current) {
      if (!empty($current['schema_cache_tags'])) {
        return Cache::mergeTags($carry, $current['schema_cache_tags']);
      }

      return $carry;
    }, $this->cacheTags);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    $definitions = $this->getDefinitions();
    $age = Cache::PERMANENT;
    foreach ($definitions as $definition) {
      if (!isset($definition['schema_cache_max_age'])) {
        continue;
      }

      // Bail out early if the cache max age is 0.
      if (($age = Cache::mergeMaxAges($age, $definition['schema_cache_max_age'])) === 0) {
        return $age;
      }
    }

    return $age;
  }
}
