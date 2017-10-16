<?php

namespace Drupal\graphql_cache_test\Cache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allow any request to be cached.
 */
class RequestPolicy implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    return static::ALLOW;
  }

}
