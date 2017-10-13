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
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Execution\QueryProcessor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The query processor.
   *
   * @var \Drupal\graphql\GraphQL\Execution\QueryProcessor
   */
  protected $queryProcessor;

  /**
   * The schema loader.
   *
   * @var \Drupal\graphql\GraphQL\Schema\SchemaLoader
   */
  protected $schemaLoader;

  /**
   * Constructs a RequestController object.
   *
   * @param \Drupal\Core\Config\Config $config
   *   The config service.
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   The http kernel service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\graphql\GraphQL\Execution\QueryProcessor $queryProcessor
   *   The query processor.
   */
  public function __construct(
    Config $config,
    HttpKernelInterface $httpKernel,
    RequestStack $requestStack,
    RendererInterface $renderer,
    QueryProcessor $queryProcessor
  ) {
    $this->config = $config;
    $this->httpKernel = $httpKernel;
    $this->requestStack = $requestStack;
    $this->renderer = $renderer;
    $this->queryProcessor = $queryProcessor;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')->get('system.performance'),
      $container->get('http_kernel'),
      $container->get('request_stack'),
      $container->get('renderer'),
      $container->get('graphql.query_processor')
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
    $filterNumeric = function($index) { return !is_numeric($index); };

    // PHP 5.5.x does not yet support the ARRAY_FILTER_USE_KEYS constant.
    $requestParameters = $request->query->all();
    $requestParametersKeys = array_filter(array_keys($requestParameters), $filterNumeric);
    $requestParameters = array_intersect_key($requestParameters, array_flip($requestParametersKeys));

    $requestContent = $request->query->all();
    $requestContentKeys = array_filter($requestContent, $filterNumeric);
    $requestContent = array_intersect_key($requestContent, array_flip($requestContentKeys));

    // Walk over all queries and issue a sub-request for each.
    $responses = array_map(function($query) use ($request, $requestParameters, $requestContent) {
      $method = $request->getMethod();

      // Make sure we remove the 'queries' parameter, otherwise the subsequent
      // request could trigger the batch processing again.
      $parameters = array_merge((array) $requestParameters, (array) $query);
      $content = $method === 'POST' ? array_merge((array) $query, (array) $requestContent) : FALSE;
      $content = $content ? json_encode($content) : '';
      $graphqlUrl = Url::fromUri('internal:/graphql')->toString(TRUE)->getGeneratedUrl();

      $subRequest = Request::create(
        $graphqlUrl,
        $method,
        $parameters,
        $request->cookies->all(),
        $request->files->all(),
        $request->server->all(),
        $content
      );

      if ($session = $request->getSession()) {
        $subRequest->setSession($session);
      }

      $output = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

      // TODO:
      // Remove the request stack manipulation once the core issue described at
      // https://www.drupal.org/node/2613044 is resolved.
      while ($this->requestStack->getCurrentRequest() === $subRequest) {
        $this->requestStack->pop();
      }

      return $output;
    }, $queries);

    // Gather all responses from all sub-requests.
    $content = array_map(function(Response $response) {
      return $response->getStatusCode() === 200 ? json_decode($response->getContent()) : NULL;
    }, $responses);

    $metadata = new CacheableMetadata();
    // Default to permanent cache.
    $metadata->setCacheMaxAge(Cache::PERMANENT);

    // Collect all of the metadata from all sub-requests.
    $metadata = array_reduce($responses, function(RefinableCacheableDependencyInterface $carry, $current) {
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
   * @param string $schema
   *   The name of the graphql schema.
   * @param string $query
   *   The query string.
   * @param array $variables
   *   The variables to process the query string with.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   The JSON formatted response.
   */
  public function handleRequest($schema, $query = '', array $variables = []) {
    /** @var \Drupal\graphql\GraphQL\Execution\QueryResult $result */
    $result = NULL;
    $context = new RenderContext();

    // Evaluating the GraphQL request can potentially invoke rendering. We allow
    // those to "leak" and collect them here in a render context.
    $this->renderer->executeInRenderContext($context, function() use ($schema, $query, $variables, &$result) {
      $result = $this->queryProcessor->processQuery($schema, $query, $variables);
    });

    $response = new CacheableJsonResponse($result->getData());
    // Apply the metadata to the response object.
    $response->addCacheableDependency($result);

    // Apply rendere context cache metadata to the response.
    if (!$context->isEmpty()) {
      $response->addCacheableDependency($context->pop());
    }

    return $response;
  }
}
