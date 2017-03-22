<?php

namespace Drupal\graphql\Cache\RequestPolicy;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Prevent the page cache from caching any graphql requests.
 */
class DenyPageCache implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    if ($request->getPathInfo() === '/graphql') {
      return static::DENY;
    }

    return NULL;
  }

}
