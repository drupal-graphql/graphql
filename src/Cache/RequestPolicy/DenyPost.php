<?php

namespace Drupal\graphql\Cache\RequestPolicy;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Contains a request policy that prevents caching of GraphQL POST requests.
 */
class DenyPost implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    if ($request->attributes->has('_graphql') && $request->getMethod() === Request::METHOD_POST) {
      return static::DENY;
    }
  }

}
