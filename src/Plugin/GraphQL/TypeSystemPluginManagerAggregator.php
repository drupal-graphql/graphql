<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Component\Plugin\PluginManagerInterface;

class TypeSystemPluginManagerAggregator implements \IteratorAggregate {

  /**
   * List of registered plugin managers.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface[]
   */
  protected $pluginManagers = [];

  /**
   * @param \Drupal\Component\Plugin\PluginManagerInterface $pluginManager
   */
  public function addPluginManager(PluginManagerInterface $pluginManager) {
    $this->pluginManagers[] = $pluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    return new \ArrayIterator($this->pluginManagers);
  }
}
