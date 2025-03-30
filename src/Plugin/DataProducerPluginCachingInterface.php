<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin;

/**
 * Defines a cacheable data producer plugins.
 */
interface DataProducerPluginCachingInterface extends DataProducerPluginInterface {

  /**
   * Calculates a cache prefix.
   */
  public function edgeCachePrefix(): ?string;

}
