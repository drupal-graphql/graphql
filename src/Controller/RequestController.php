<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\graphql\GraphQL\Execution\QueryProcessor;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   *
   * @codeCoverageIgnore
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
   *
   * @codeCoverageIgnore
   */
  public function __construct(QueryProcessor $processor, array $parameters) {
    $this->processor = $processor;
    $this->parameters = $parameters;
  }

  /**
   * Handles graphql requests.
   *
   * @param string $server
   *   The name of the server.
   * @param \GraphQL\Server\OperationParams|\GraphQL\Server\OperationParams[] $operations
   *   The graphql operation(s) to execute.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   The JSON formatted response.
   *
   * @throws \Exception
   */
  public function handleRequest($server, $operations) {
    if (is_array($operations)) {
      return $this->handleBatch($server, $operations);
    }

    return $this->handleSingle($server, $operations);
  }

  /**
   * @param $server
   * @param $operations
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   * @throws \Exception
   */
  protected function handleSingle($server, $operations) {
    $result = $this->processor->processQuery($server, $operations);
    $response = new CacheableJsonResponse($result);
    $response->addCacheableDependency($result);
    return $response;
  }

  /**
   * @param $server
   * @param $operations
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   * @throws \Exception
   */
  protected function handleBatch($server, $operations) {
    $result = $this->processor->processQuery($server, $operations);
    $response = new CacheableJsonResponse($result);

    // In case of a batch request, the result is an array.
    $dependencies = is_array($operations) ? $result : [$result];
    foreach ($dependencies as $dependency) {
      $response->addCacheableDependency($dependency);
    }

    return $response;
  }

}
