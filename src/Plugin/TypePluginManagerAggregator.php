<?php

namespace Drupal\graphql\Plugin;

class TypePluginManagerAggregator implements \IteratorAggregate {

  /**
   * List of registered plugin managers.
   *
   * @var \Drupal\graphql\Plugin\TypePluginManagerInterface[]
   */
  protected $pluginManagers = [];

  /**
   * Registers a plugin manager.
   *
   * @param \Drupal\graphql\Plugin\TypePluginManagerInterface $pluginManager
   *   The plugin manager to register.
   * @param $id
   *   The id of the service.
   */
  public function addTypeManager(TypePluginManagerInterface $pluginManager, $id) {
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
   * @return \Drupal\graphql\Plugin\TypePluginManagerInterface
   *   The plugin managers for the given plugin type.
   */
  public function getTypeManager($type) {
    if (isset($this->pluginManagers[$type])) {
      return $this->pluginManagers[$type];
    }

    throw new \LogicException(sprintf("Couldn't find a plugin manager for type '%s'.", $type));
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    return new \ArrayIterator($this->pluginManagers);
  }
}