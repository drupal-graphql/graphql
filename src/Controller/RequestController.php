<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\Config;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\graphql\GraphQL\Execution\Processor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Schema\Schema;

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
   * The
   *
   * @var \Youshido\GraphQL\Schema\AbstractSchema
   */
  protected $schema;

  /**
   * Constructs a RequestController object.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param \Youshido\GraphQL\Schema\AbstractSchema $schema
   * @param \Drupal\Core\Config\Config $config
   */
  public function __construct(ContainerInterface $container, AbstractSchema $schema, Config $config) {
    $this->container = $container;
    $this->schema = $schema;
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container,
      $container->get('graphql.schema'),
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


    $processor = new Processor($this->container, $this->schema);
    $result = $processor->processPayload($query, $variables);
    $response = new CacheableJsonResponse($result->getResponseData());

    // @todo This needs proper, context and tag based caching logic.
    //
    // We need to figure out a way to make this work with the dynamic page cache
    // and fractional caching. For now, we only cache for anonymous users via
    // the custom CacheSubscriber provided by this module. Not cool.
    $metadata = new CacheableMetadata();
    // Default to permanent cache.
    $metadata->setCacheMaxAge(Cache::PERMANENT);
    // Add cache metadata from the processor and result stages.
    $metadata->addCacheableDependency($processor);
    $metadata->addCacheableDependency($result);
    // Apply the metadata to the response object.
    $response->addCacheableDependency($metadata);

    // Set the execution context on the request attributes for use in the
    // request subscriber and cache policies.
    $request->attributes->set('context', $result->getExecutionContext());

    return $response;
  }
}
