<?php

namespace Drupal\graphql\Cache\RequestPolicy;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Request policy for allowing GraphQL queries to be cached.
 */
class DenyUnsafeMethodUnlessQuery implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    if (!$request->isMethodSafe() && !($request->getMethod() === 'POST' && $request->getPathInfo() === '/graphql')) {
      return static::DENY;
    }

    return NULL;
  }

}
