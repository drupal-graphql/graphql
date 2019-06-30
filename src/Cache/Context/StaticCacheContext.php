<?php

namespace Drupal\graphql\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;

class StaticCacheContext implements CalculatedCacheContextInterface {

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Static');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($parameter = NULL) {
    return $parameter ?: '';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($parameter = NULL) {
    return new CacheableMetadata();
  }

}