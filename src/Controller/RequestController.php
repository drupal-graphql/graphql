<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\graphql\Entity\ServerInterface;
use GraphQL\Server\OperationParams;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RequestController implements ContainerInjectionInterface {

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
    return new static($container->getParameter('graphql.config'));
  }

  /**
   * RequestController constructor.
   *
   * @param array $parameters
   *   The service configuration parameters.
   *
   * @codeCoverageIgnore
   */
  public function __construct(array $parameters) {
    $this->parameters = $parameters;
  }

  /**
   * Handles graphql requests.
   *
   * @param \Drupal\graphql\Entity\ServerInterface $graphql_server
   *   The server instance.
   * @param \GraphQL\Server\OperationParams|\GraphQL\Server\OperationParams[] $operations
   *   The graphql operation(s) to execute.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   The JSON formatted response.
   *
   * @throws \Exception
   */
  public function handleRequest(ServerInterface $graphql_server, $operations) {
    if (is_array($operations)) {
      return $this->handleBatch($graphql_server, $operations);
    }

    /** @var \GraphQL\Server\OperationParams $operations */
    return $this->handleSingle($graphql_server, $operations);
  }

  /**
   * @param \Drupal\graphql\Entity\ServerInterface $server
   * @param \GraphQL\Server\OperationParams $operation
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   * @throws \Exception
   */
  protected function handleSingle(ServerInterface $server, OperationParams $operation) {
    $result = $server->executeOperation($operation);
    $response = new CacheableJsonResponse($result);
    $response->addCacheableDependency($result);
    return $response;
  }

  /**
   * @param \Drupal\graphql\Entity\ServerInterface $server
   * @param \GraphQL\Server\OperationParams[] $operations
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   * @throws \Exception
   */
  protected function handleBatch(ServerInterface $server, array $operations) {
    $result = $server->executeBatch($operations);
    $response = new CacheableJsonResponse($result);

    // In case of a batch request, the result is an array.
    foreach ($result as $dependency) {
      $response->addCacheableDependency($dependency);
    }

    return $response;
  }

}
