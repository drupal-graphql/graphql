<?php

namespace Drupal\graphql\EventSubscriber;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Disables any display variant on the explorer page.
 */
class CacheSubscriber implements EventSubscriberInterface {
  /**
   * The request policy service.
   *
   * @var \Drupal\Core\PageCache\RequestPolicyInterface
   */
  protected $requestPolicy;

  /**
   * The response policy service.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicyInterface
   */
  protected $responsePolicy;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new DynamicPageCacheSubscriber object.
   *
   * @param \Drupal\Core\PageCache\RequestPolicyInterface $requestPolicy
   *   A policy rule determining the cacheability of a request.
   * @param \Drupal\Core\PageCache\ResponsePolicyInterface $responsePolicy
   *   A policy rule determining the cacheability of the response.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   */
  public function __construct(RequestPolicyInterface $requestPolicy, ResponsePolicyInterface $responsePolicy, RouteMatchInterface $routeMatch) {
    $this->requestPolicy = $requestPolicy;
    $this->responsePolicy = $responsePolicy;
    $this->routeMatch = $routeMatch;
  }

  /**
   * Sets a response in case of a cache hit.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function onRouteMatch(GetResponseEvent $event) {
    if ($this->routeMatch->getRouteName() !== 'graphql.request') {
      return;
    }

    // @todo Handle cache writes here.
  }

  /**
   * Stores a response in case of a cache miss if applicable.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The event to process.
   */
  public function onResponse(FilterResponseEvent $event) {
    if ($this->routeMatch->getRouteName() !== 'graphql.request') {
      return;
    }

    // @todo Handle cache writes here.
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Run after AuthenticationSubscriber (necessary for the 'user' cache
    // context; priority 300) and MaintenanceModeSubscriber (Dynamic Page Cache
    // should not be polluted by maintenance mode-specific behavior; priority
    // 30), but before ContentControllerSubscriber (updates _controller, but
    // that is a no-op when Dynamic Page Cache runs; priority 25).
    $events[KernelEvents::REQUEST][] = ['onRouteMatch', 27];

    // Run before HtmlResponseSubscriber::onRespond(), which has priority 0.
    $events[KernelEvents::RESPONSE][] = ['onResponse', 100];

    return $events;
  }
}
