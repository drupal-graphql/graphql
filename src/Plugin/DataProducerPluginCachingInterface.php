<?php

namespace Drupal\graphql\Plugin;

interface DataProducerPluginCachingInterface extends DataProducerPluginInterface {

  /**
   * @return string|null
   *   Returns a string or null.
   */
  public function edgeCachePrefix();

}
