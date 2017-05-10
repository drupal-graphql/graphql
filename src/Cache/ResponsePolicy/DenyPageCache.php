<?php

namespace Drupal\graphql\Cache\ResponsePolicy;

use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\Core\Routing\StackedRouteMatchInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prevent the page cache from caching any graphql responses.
 */
class DenyPageCache implements ResponsePolicyInterface  {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\StackedRouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new request policy instance.
   *
   * @param \Drupal\Core\Routing\StackedRouteMatchInterface $routeMatch
   *   The route provider service.
   */
  public function __construct(StackedRouteMatchInterface $routeMatch) {
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public function check(Response $response, Request $request) {
    if ($this->routeMatch->getRouteMatchFromRequest($request) !== 'graphql.request') {
      return static::DENY;
    }

    return NULL;
  }

}
