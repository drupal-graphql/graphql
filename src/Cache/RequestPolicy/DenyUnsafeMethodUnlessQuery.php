<?php

namespace Drupal\graphql\Cache\RequestPolicy;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Request policy for allowing GraphQL queries to be cached.
 */
class DenyUnsafeMethodUnlessQuery implements RequestPolicyInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new request policy instance.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route provider service.
   */
  public function __construct(RouteMatchInterface $routeMatch) {
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    if (!$request->isMethodSafe() && !($request->getMethod() === 'POST' && $this->routeMatch->getRouteName() === 'graphql.request')) {
      return static::DENY;
    }

    return NULL;
  }

}
