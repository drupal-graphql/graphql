<?php

namespace Drupal\graphql\Plugin;

interface DataProducerPluginCachingInterface extends DataProducerPluginInterface {

  /**
   * @return string|null
   */
  public function edgeCachePrefix();

}
