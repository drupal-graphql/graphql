<?php

namespace Drupal\graphql\Plugin;

/**
 * Defines cachable data producer plugins.
 */
interface DataProducerPluginCachingInterface extends DataProducerPluginInterface {

  /**
   * Calculates a cache prefix.
   *
   * @return string|null
   */
  public function edgeCachePrefix();

}
