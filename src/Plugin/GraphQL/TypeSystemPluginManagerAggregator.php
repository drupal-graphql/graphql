<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;

class TypeSystemPluginManagerAggregator implements \IteratorAggregate {

  /**
   * List of registered plugin managers.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerInterface[][]
   */
  protected $pluginManagers = [];

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The cached definitions.
   *
   * @var array
   */
  protected $definitions;

  /**
   * TypeSystemPluginManagerAggregator constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend.
   */
  public function __construct(CacheBackendInterface $cacheBackend) {
    $this->cacheBackend = $cacheBackend;
  }

  /**
   * Registers a plugin manager.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerInterface $pluginManager
   *   The plugin manager to register.
   * @param $id
   *   The id of the service.
   */
  public function addPluginManager(TypeSystemPluginManagerInterface $pluginManager, $id) {
    $pieces = explode('.', $id);
    $key = end($pieces);

    $this->pluginManagers[$key][] = $pluginManager;
  }

  /**
   * Retrieves a plugin manager by its type.
   *
   * @param string $type
   *   The plugin type.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerInterface[]
   *   The plugin managers for the given plugin type.
   */
  public function getPluginManagers($type) {
    if (isset($this->pluginManagers[$type])) {
      return $this->pluginManagers[$type];
    }

    return [];
  }

  /**
   * @param \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerInterface $manager
   *
   * @return array|null
   */
  public function cacheGet(TypeSystemPluginManagerInterface $manager) {
    $key = $manager->getCacheKey();

    if (!isset($this->definitions)) {
      // Fetch the definitions for all plugin managers.
      $keys = $this->getCacheIdentifiers();
      $defaults = array_fill_keys($keys, NULL);
      $results = array_map(function ($item) {
        return $item->data;
      }, $this->cacheBackend->getMultiple($keys));

      $this->definitions = array_merge($defaults, $results);
    }

    return $this->definitions[$key];
  }

  /**
   * @param \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerInterface $manager
   * @param $definition
   *
   * @return $this
   */
  public function cacheSet(TypeSystemPluginManagerInterface $manager, $definition) {
    $key = $manager->getCacheKey();
    $tags = $manager->getCacheTags();
    $this->definitions[$key] = $definition;
    $this->cacheBackend->set($key, $this->definitions[$key], Cache::PERMANENT, $tags);

    return $this;
  }

  /**
   * @return array
   */
  protected function getCacheIdentifiers() {
    $keys = [];
    foreach ($this->pluginManagers as $managers) {
      foreach ($managers as $manager) {
        $keys[] = $manager->getCacheKey();
      }
    }

    return $keys;
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    return new \ArrayIterator($this->pluginManagers);
  }
}
