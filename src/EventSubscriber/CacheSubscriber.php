<?php

namespace Drupal\graphql\EventSubscriber;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Disables any display variant on the explorer page.
 */
class CacheSubscriber implements EventSubscriberInterface {

  /**
   * Name of Dynamic Page Cache's response header.
   */
  const HEADER = 'X-Drupal-GraphQL-Cache';

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
   * The request policy results.
   *
   * @var \SplObjectStorage
   */
  protected $requestPolicyResults;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $requestCache;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new CacheSubscriber object.
   *
   * @param \Drupal\Core\PageCache\RequestPolicyInterface $requestPolicy
   *   A policy rule determining the cacheability of a request.
   * @param \Drupal\Core\PageCache\ResponsePolicyInterface $responsePolicy
   *   A policy rule determining the cacheability of the response.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Cache\CacheBackendInterface $requestCache
   *   The cache backend.
   */
  public function __construct(RequestPolicyInterface $requestPolicy, ResponsePolicyInterface $responsePolicy, RouteMatchInterface $routeMatch, RequestStack $requestStack, CacheBackendInterface $requestCache) {
    $this->requestPolicy = $requestPolicy;
    $this->responsePolicy = $responsePolicy;
    $this->routeMatch = $routeMatch;
    $this->requestCache = $requestCache;
    $this->requestStack = $requestStack;
    $this->requestPolicyResults = new \SplObjectStorage();
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

    // Don't cache the response if the request policies are not met. Store the
    // result in a static keyed by current request, so that onResponse() does
    // not have to redo the request policy check.
    $request = $event->getRequest();
    $requestPolicyResult = $this->requestPolicy->check($request);
    $this->requestPolicyResults[$request] = $requestPolicyResult;
    if ($requestPolicyResult !== RequestPolicyInterface::ALLOW) {
      return;
    }

    if (empty($request->attributes->get('query')) && empty($request->attributes->get('queries'))) {
      return;
    }

    $cid = $this->getCacheIdentifier($request);
    if (($cache = $this->requestCache->get($cid)) && $cache->data instanceof Response) {
      $response = $cache->data;
      $response->headers->set(self::HEADER, 'HIT');
      $event->setResponse($response);
    }
  }

  /**
   * Stores a response in case of a cache miss if applicable.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The event to process.;
   */
  public function onResponse(FilterResponseEvent $event) {
    if ($this->routeMatch->getRouteName() !== 'graphql.request') {
      return;
    }

    $response = $event->getResponse();
    if (!$response instanceof CacheableResponseInterface) {
      return;
    }

    // There's no work left to be done if this is a cache hit.
    if ($response->headers->get(self::HEADER) === 'HIT') {
      return;
    }

    // Don't cache the response if our request subscriber did not fire, because
    // that means it is impossible to have a cache hit. This can happen when the
    // master request is for example a 403 or 404, in which case a subrequest is
    // performed by the router. In that case, it is the subrequest's response
    // that is cached by us, because the routing happens in a request subscriber
    // earlier than this one and immediately sets a response, i.e. the one
    // returned by the subrequest, and thus causes our request subscriber to not
    // fire for the master request.
    //
    // @see \Drupal\Core\Routing\AccessAwareRouter::checkAccess()
    // @see \Drupal\Core\EventSubscriber\DefaultExceptionHtmlSubscriber::on403()
    $request = $event->getRequest();
    if (!isset($this->requestPolicyResults[$request])) {
      return;
    }

    // Don't cache the response if the request & response policies are not met.
    // @see onRouteMatch()
    if ($this->requestPolicyResults[$request] !== RequestPolicyInterface::ALLOW || $this->responsePolicy->check($response, $request) === ResponsePolicyInterface::DENY) {
      return;
    }

    $cid = $this->getCacheIdentifier($request);
    $metadata = $response->getCacheableMetadata();
    $tags = $metadata->getCacheTags();
    $expire = $this->maxAgeToExpire($metadata->getCacheMaxAge());

    // Write the cache entry.
    $this->requestCache->set($cid, $response, $expire, $tags);

    // The response was generated, mark the response as a cache miss. The next
    // time, it will be a cache hit.
    $response->headers->set(self::HEADER, 'MISS');
  }

  /**
   * Maps a max age value to an "expire" value for the Cache API.
   *
   * @param int $maxAge
   *   A max age value.
   *
   * @return int
   *   A corresponding "expire" value.
   *
   * @see \Drupal\Core\Cache\CacheBackendInterface::set()
   */
  protected function maxAgeToExpire($maxAge) {
    return ($maxAge === Cache::PERMANENT) ? Cache::PERMANENT : (int) $this->requestStack->getMasterRequest()->server->get('REQUEST_TIME') + $maxAge;
  }

  /**
   * Generates a cache identifier for the current request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return string
   *   The generated cache identifier.
   */
  protected function getCacheIdentifier(Request $request) {
    if ($queries = $request->attributes->get('queries')) {
      return hash('sha256', $queries);
    }

    $query = $request->attributes->get('query');
    $variables = json_encode($request->attributes->get('variables') ?: []);
    return hash('sha256', "$query:$variables");
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Run after AuthenticationSubscriber (necessary for the 'user' cache
    // context; priority 300) and MaintenanceModeSubscriber (the cache should
    // not be polluted by maintenance mode-specific behavior; priority 30), but
    // before ContentControllerSubscriber (updates _controller, but that is a
    // no-op when the cache runs; priority 25).
    $events[KernelEvents::REQUEST][] = ['onRouteMatch', 27];

    // Run before HtmlResponseSubscriber::onRespond(), which has priority 0.
    $events[KernelEvents::RESPONSE][] = ['onResponse', 100];

    return $events;
  }
}
