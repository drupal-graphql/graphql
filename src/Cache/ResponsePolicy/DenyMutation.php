<?php

namespace Drupal\graphql\Cache\ResponsePolicy;

use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\Core\Routing\StackedRouteMatchInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Youshido\GraphQL\Execution\Context\ExecutionContext;

/**
 * Reject if the query contains a mutation.
 */
class DenyMutation implements ResponsePolicyInterface  {

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
    if ($this->routeMatch->getRouteMatchFromRequest($request)->getRouteName() !== 'graphql.request') {
      return NULL;
    }

    if (!$request->attributes->has('graphql_execution_context')) {
      return NULL;
    }

    $context = $request->attributes->get('graphql_execution_context');
    if ($context && $context instanceof ExecutionContext) {
      if (($query = $context->getRequest()) && $query->hasMutations()) {
        return static::DENY;
      }
    }

    return NULL;
  }
}
