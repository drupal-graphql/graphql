<?php

namespace Drupal\graphql\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;

/**
 * Helper class, not sure for what.
 *
 * @todo This class and service seems unused, can we remove it?
 */
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
