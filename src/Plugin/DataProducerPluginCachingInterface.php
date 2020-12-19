<?php

namespace Drupal\graphql\Plugin;

/**
 * Defines cachable data producer plugins.
 */
interface DataProducerPluginCachingInterface extends DataProducerPluginInterface {

  /**
   * @return string|null
   */
  public function edgeCachePrefix();

}
