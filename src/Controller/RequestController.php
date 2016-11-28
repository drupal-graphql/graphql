<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\Config;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Youshido\GraphQL\Execution\Processor;

/**
 * Handles GraphQL requests.
 */
class RequestController implements ContainerInjectionInterface {

  /**
   * The processor service.
   *
   * @var \Youshido\GraphQL\Execution\Processor
   */
  protected $processor;

  /**
   * The system.performance config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs a RequestController object.
   *
   * @param \Youshido\GraphQL\Execution\Processor $processor
   * @param \Drupal\Core\Config\Config $config
   */
  public function __construct(Processor $processor, Config $config) {
    $this->processor = $processor;
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('graphql.processor'),
      $container->get('config.factory')->get('system.performance')
    );
  }

  /**
   * Handles GraphQL requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON formatted response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function handleRequest(Request $request) {
    $body = (array) json_decode($request->getContent()) + [
      'query' => NULL,
      'variables' => NULL,
    ];

    $query = $request->query->has('query') ? $request->query->get('query') : $body['query'];
    $variables = $request->query->has('variables') ? $request->query->get('variables') : $body['variables'];

    if (empty($query)) {
      throw new NotFoundHttpException();
    }

    $variables = ($variables && is_string($variables) ? json_decode($variables) : $variables);
    $variables = (array) ($variables ?: []);
    $result = $this->processor->processPayload($query, $variables);
    $response = new CacheableJsonResponse($result->getResponseData());

    // @todo This needs proper, context and tag based caching logic.
    //
    // We need to add cache contexts and tags from the resolver functions in the
    // schema and figure out a way to make this work with the dynamic page cache
    // and fractional caching. For now, we only cache for anonymous users via
    // the custom CacheSubscriber provided by this module. Not cool.
    $metadata = new CacheableMetadata();
    $metadata->setCacheMaxAge(Cache::PERMANENT);
    $response->addCacheableDependency($metadata);

    // Set the execution context on the request attributes for use in the
    // request subscriber and cache policies.
    $request->attributes->set('context', $result->getExecutionContext());

    return $response;
  }
}
