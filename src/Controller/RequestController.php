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
   * The service configuration parameters.
   *
   * @var array
   */
  protected $parameters;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('graphql.query_processor'),
      $container->getParameter('graphql.config')
    );
  }

  /**
   * RequestController constructor.
   *
   * @param \Drupal\graphql\GraphQL\Execution\QueryProcessor $processor
   *   The query processor.
   * @param array $parameters
   *   The service configuration parameters.
   */
  public function __construct(QueryProcessor $processor, array $parameters) {
    $this->processor = $processor;
    $this->parameters = $parameters;
  }

  /**
   * Handles graphql requests.
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
    $debug = !empty($this->parameters['development']);
    $result = $this->processor->processQuery($schema, $operations, NULL, $debug);
    $response = new CacheableJsonResponse($result);
    $response->addCacheableDependency($result);
    return $response;
  }
}
