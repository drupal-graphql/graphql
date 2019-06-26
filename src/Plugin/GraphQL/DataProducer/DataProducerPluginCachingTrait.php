<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

trait DataProducerPluginCachingTrait {

  /**
   * {@inheritdoc}
   */
  public function edgeCachePrefix() {
    return hash('sha256', serialize($this->getContextValues()));
  }

  /**
   * {@inheritdoc}
   */
  abstract public function getContextValues();

}