<?php

namespace Drupal\graphql\Plugin;

interface DataProducerPluginCachingInterface extends DataProducerPluginInterface {

  /**
   * @return boolean
   */
  public function useEdgeCache();

  /**
   * @return string|null
   */
  public function edgeCachePrefix();

}
