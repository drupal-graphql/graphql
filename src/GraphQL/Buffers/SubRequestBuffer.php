<?php

namespace Drupal\graphql\GraphQL\Buffers;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SubRequestBuffer extends BufferBase {

  /**
   * The http kernel service.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * SubrequestBuffer constructor.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   The http kernel service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   */
  public function __construct(HttpKernelInterface $httpKernel, RequestStack $requestStack) {
    $this->httpKernel = $httpKernel;
    $this->requestStack = $requestStack;
  }

  /**
   * Add an item to the buffer.
   *
   * @param \Drupal\Core\Url $url
   *   The url object to run the subrequest on.
   * @param callable $extract
   *   The callback to run within the sub-request.
   *
   * @return \Closure
   *   The callback to invoke to load the result for this buffer item.
   */
  public function add(Url $url, callable $extract) {
    $item = new \ArrayObject([
      'url' => $url,
      'extract' => $extract,
    ]);

    return $this->createBufferResolver($item);
  }

  /**
   * {@inheritdoc}
   */
  protected function getBufferId($item) {
    /** @var \Drupal\Core\GeneratedUrl $url */
    $url = $item['url']->toString(TRUE);

    return hash('sha256', json_encode([
      'url' => $url->getGeneratedUrl(),
      'tags' => $url->getCacheTags(),
      'contexts' => $url->getCacheContexts(),
      'age' => $url->getCacheMaxAge(),
    ]));
  }

  /**
   * {@inheritdoc}
   */
  public function resolveBufferArray(array $buffer) {
    /** @var \Drupal\Core\GeneratedUrl $url */
    $url = reset($buffer)['url']->toString(TRUE);

    $currentRequest = $this->requestStack->getCurrentRequest();
    $request = Request::create(
      $url->getGeneratedUrl(),
      'GET',
      $currentRequest->query->all(),
      $currentRequest->cookies->all(),
      $currentRequest->files->all(),
      $currentRequest->server->all()
    );

    $request->attributes->set('_graphql_subrequest', function () use ($buffer) {
      return array_map(function ($item) {
        return $item['extract']($item['url']);
      }, $buffer);
    });

    if ($session = $currentRequest->getSession()) {
      $request->setSession($session);
    }

    /** @var \Drupal\graphql\GraphQL\Buffers\SubRequestResponse $response */
    $response = $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    if ($url instanceof CacheableDependencyInterface) {
      $response->addCacheableDependency($url);
    }

    // TODO:
    // Remove the request stack manipulation once the core issue described at
    // https://www.drupal.org/node/2613044 is resolved.
    while ($this->requestStack->getCurrentRequest() === $request) {
      $this->requestStack->pop();
    }

    return array_map(function ($value) use ($response) {
      return new CacheableValue($value, [$response]);
    }, $response->getResult());
  }

}