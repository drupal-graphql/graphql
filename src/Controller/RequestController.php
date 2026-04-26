<?php

declare(strict_types=1);

namespace Drupal\graphql\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\graphql\Entity\ServerInterface;
use GraphQL\Server\OperationParams;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The main GraphQL request handler that will forward to the responsible server.
 */
class RequestController implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container): self {
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
  public function __construct(
    protected array $parameters,
  ) {
  }

  /**
   * Handles graphql requests.
   *
   * @param \Drupal\graphql\Entity\ServerInterface $graphql_server
   *   The server instance.
   * @param \GraphQL\Server\OperationParams|array<\GraphQL\Server\OperationParams> $operations
   *   The graphql operation(s) to execute.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   The JSON formatted response.
   *
   * @throws \Exception
   */
  public function handleRequest(ServerInterface $graphql_server, OperationParams|array $operations): CacheableJsonResponse {
    if (is_array($operations)) {
      return $this->handleBatch($graphql_server, $operations);
    }

    /** @var \GraphQL\Server\OperationParams $operations */
    return $this->handleSingle($graphql_server, $operations);
  }

  /**
   * Execute a single operation and turn that into a cacheable response.
   *
   * @throws \Exception
   */
  protected function handleSingle(ServerInterface $server, OperationParams $operation): CacheableJsonResponse {
    $result = $server->executeOperation($operation);
    $response = new CacheableJsonResponse($result);
    $response->addCacheableDependency($result);
    return $response;
  }

  /**
   * Execute multiple operations as batch and turn that into cacheable response.
   *
   * @param \Drupal\graphql\Entity\ServerInterface $server
   *   The graphql server.
   * @param array<\GraphQL\Server\OperationParams> $operations
   *   The list of operations to execute.
   *
   * @throws \Exception
   */
  protected function handleBatch(ServerInterface $server, array $operations): CacheableJsonResponse {
    $result = $server->executeBatch($operations);
    $response = new CacheableJsonResponse($result);

    // In case of a batch request, the result is an array.
    foreach ($result as $dependency) {
      $response->addCacheableDependency($dependency);
    }

    return $response;
  }

}
