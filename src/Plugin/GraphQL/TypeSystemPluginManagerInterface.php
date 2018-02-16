<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;

interface TypeSystemPluginManagerInterface extends PluginManagerInterface, CacheableDependencyInterface {

  /**
   * @return mixed
   */
  public function getCacheKey();

  /**
   * Returns the type of plugin handled by this plugin manager.
   *
   * @return string
   *   The plugin type handled by this manager.
   */
  public function getPluginType();

}
