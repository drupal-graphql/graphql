<?php

namespace Drupal\graphql\GraphQL\Cache;

class UncacheableValue extends CacheableValue {

  /**
   * {@inheritdoc}
   */
  protected $cacheMaxAge = 0;

}
