<?php

declare(strict_types=1);

namespace Drupal\graphql\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\graphql\GraphQL\Execution\ExecutionResult;
use Drupal\graphql\Plugin\PersistedQueryPluginInterface;
use GraphQL\Server\OperationParams;
use GraphQL\Server\ServerConfig;

/**
 * Defines a GraphQL server that has configuration and executes queries.
 */
interface ServerInterface extends ConfigEntityInterface {

  /**
   * Execute an operation on this server.
   *
   * @param \GraphQL\Server\OperationParams $operation
   *   The operation parameters.
   *
   * @return \Drupal\graphql\GraphQL\Execution\ExecutionResult
   *   The execution result.
   */
  public function executeOperation(OperationParams $operation): ExecutionResult;

  /**
   * Execute multiple operations as batch on this server.
   *
   * @param array<\GraphQL\Server\OperationParams> $operations
   *   A list of operations to execute in the batch.
   *
   * @return array<\Drupal\graphql\GraphQL\Execution\ExecutionResult>
   *   The execution results for each operation.
   */
  public function executeBatch(array $operations): array;

  /**
   * Retrieves the server configuration.
   *
   * @return \GraphQL\Server\ServerConfig
   *   The server configuration.
   */
  public function configuration(): ServerConfig;

  /**
   * Adds a Persisted Query plugin instance to the persisted queries set.
   */
  public function addPersistedQueryInstance(PersistedQueryPluginInterface $queryPlugin): void;

  /**
   * Removes a Persisted Query plugin instance from the persisted queries set.
   *
   * @param string $queryPluginId
   *   The plugin id to be removed.
   */
  public function removePersistedQueryInstance(string $queryPluginId): void;

  /**
   * Removes all the persisted query instances.
   */
  public function removeAllPersistedQueryInstances(): void;

  /**
   * Returns the current persisted queries set, sorted by the plugins weight.
   *
   * @return array<\Drupal\graphql\Plugin\PersistedQueryPluginInterface>
   *   The persisted query plugin instances, sorted by weight.
   */
  public function getPersistedQueryInstances(): array;

}
