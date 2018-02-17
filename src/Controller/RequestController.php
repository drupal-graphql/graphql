<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\graphql\GraphQL\Execution\QueryProcessor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles GraphQL requests.
 */
class RequestController implements ContainerInjectionInterface {

  /**
   * The query processor.
   *
   * @var \Drupal\graphql\GraphQL\Execution\QueryProcessor
   */
  protected $processor;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('graphql.query_processor'));
  }

  /**
   * RequestController constructor.
   *
   * @param \Drupal\graphql\GraphQL\Execution\QueryProcessor $processor
   *   The query processor.
   */
  public function __construct(QueryProcessor $processor) {
    $this->processor = $processor;
  }

  /**
   * Handles GraphQL requests.
   *
   * @param string $schema
   *   The name of the schema.
   * @param \GraphQL\Server\OperationParams|\GraphQL\Server\OperationParams[] $operations
   *   The graphql operation(s) to execute.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   The JSON formatted response.
   */
  public function handleRequest($schema, $operations) {
    $result = $this->processor->processQuery($schema, $operations);
    $response = new CacheableJsonResponse($result);
    $response->addCacheableDependency($result);
    return $response;
  }
}
