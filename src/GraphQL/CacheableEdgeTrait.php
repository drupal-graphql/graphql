<?php

namespace Drupal\graphql\GraphQL;

use Youshido\GraphQL\Schema\AbstractSchema;

trait CacheableEdgeTrait {

  /**
   * Retrieves the referenced plugin instance.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface
   *   The referenced plugin instance.
   */
  abstract public function getPlugin();

  /**
   * {@inheritdoc}
   */
  public function getSchemaCacheMetadata() {
    return $this->getPlugin()->getSchemaCacheMetadata();
  }

  /**
   * {@inheritdoc}
   */
  public function getResponseCacheMetadata() {
    return $this->getPlugin()->getResponseCacheMetadata();
  }

}