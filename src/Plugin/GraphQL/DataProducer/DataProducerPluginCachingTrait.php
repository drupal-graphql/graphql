<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

trait DataProducerPluginCachingTrait {

  /**
   * {@inheritdoc}
   */
  public function edgeCachePrefix() {
    return md5(serialize($this->getContextValues()));
  }

  /**
   * {@inheritdoc}
   */
  abstract public function getContextValues();

}