<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\graphql\GraphQL\Execution\Processor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Youshido\GraphQL\Schema\AbstractSchema;

/**
 * Handles GraphQL requests.
 */
class RequestController implements ContainerInjectionInterface {

  /**
   * The system.performance config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * The graphql schema.
   *
   * @var \Youshido\GraphQL\Schema\AbstractSchema
   */
  protected $schema;

  /**
   * The http kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a RequestController object.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param \Youshido\GraphQL\Schema\AbstractSchema $schema
   * @param \Drupal\Core\Config\Config $config
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   */
  public function __construct(ContainerInterface $container, AbstractSchema $schema, Config $config, HttpKernelInterface $httpKernel, RendererInterface $renderer) {
    $this->container = $container;
    $this->schema = $schema;
    $this->config = $config;
    $this->httpKernel = $httpKernel;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container,
      $container->get('graphql.schema'),
      $container->get('config.factory')->get('system.performance'),
      $container->get('http_kernel'),
      $container->get('renderer')
    );
  }

  /**
   * Handles GraphQL batch requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param array $queries
   *   An array of queries.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON formatted response.
   */
  public function handleBatchRequest(Request $request, array $queries = []) {
    // Walk over all queries and issue a sub-request for each.
    $responses = array_map(function ($query) use ($request) {
      $method = $request->getMethod();
      $parameters = $method === 'GET' ? $query : [];
      $content = $method === 'POST' ? json_encode($query) : '';
      $subRequest = Request::create('/graphql', $method, $parameters, $request->cookies->all(), $request->files->all(), $request->server->all(), $content);
      $subRequest->setSession($request->getSession());
      return $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }, $queries);

    // Gather all responses from all sub-requests.
    $content = array_map(function (Response $response) {
      return json_decode($response->getContent());
    }, $responses);

    $metadata = new CacheableMetadata();
    // Default to permanent cache.
    $metadata->setCacheMaxAge(Cache::PERMANENT);

    // Collect all of the metadata from all sub-requests.
    $metadata = array_reduce($responses, function (RefinableCacheableDependencyInterface $carry, $current) {
      $current = $current instanceof CacheableResponseInterface ? $current->getCacheableMetadata() : $current;
      $carry->addCacheableDependency($current);
      return $carry;
    }, $metadata);

    $response = new CacheableJsonResponse($content);
    $response->addCacheableDependency($metadata);

    return $response;
  }

  /**
   * Handles GraphQL requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param string $query
   *   The query string.
   * @param array $variables
   *   The variables to process the query string with.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON formatted response.
   */
  public function handleRequest(Request $request, $query = '', array $variables = []) {
    $context = new RenderContext();
    $processor = new Processor($this->container, $this->schema);

    // Evaluating the GraphQL request can potentially invoke rendering. We allow
    // those to "leak" and collect them here in a render context.
    $this->renderer->executeInRenderContext($context, function () use ($processor, $query, $variables) {
      $processor->processPayload($query, $variables);
    });

    $result = $processor->getResponseData();
    $response = new CacheableJsonResponse($result);
    if (!$context->isEmpty()) {
      $response->addCacheableDependency($context->pop());
    }

    // @todo This needs proper, context and tag based caching logic.
    //
    // We need to figure out a way to make this work with the dynamic page cache
    // and fractional caching. For now, we only cache for anonymous users via
    // the custom CacheSubscriber provided by this module. Not cool.
    $metadata = new CacheableMetadata();
    // Default to permanent cache.
    $metadata->setCacheMaxAge(Cache::PERMANENT);
    // Add cache metadata from the processor and result stages.
    $metadata->addCacheableDependency($result);
    $metadata->addCacheableDependency($processor);
    // Apply the metadata to the response object.
    $response->addCacheableDependency($metadata);

    // Set the execution context on the request attributes for use in the
    // request subscriber and cache policies.
    $request->attributes->set('context', $processor->getExecutionContext());

    return $response;
  }
}
