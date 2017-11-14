<?php

namespace Drupal\graphql\Plugin\GraphQL;

class TypeSystemPluginManagerAggregator implements \IteratorAggregate {

  /**
   * List of registered plugin managers.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerInterface[]
   */
  protected $pluginManagers = [];

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

    $this->pluginManagers[$key] = $pluginManager;
  }

  /**
   * Retrieves a plugin manager by its type.
   *
   * @param string $type
   *   The plugin type.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerInterface|null
   *   The plugin manager for the given plugin type or NULL.
   */
  public function getPluginManager($type) {
    if (isset($this->pluginManagers[$type])) {
      return $this->pluginManagers[$type];
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    return new \ArrayIterator($this->pluginManagers);
  }
}
