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
   */
  public function executeOperation(OperationParams $operation): ExecutionResult;

  /**
   * Execute multiple operations as batch on this server.
   *
   * @param array<\GraphQL\Server\OperationParams> $operations
   *
   * @return array<\Drupal\graphql\GraphQL\Execution\ExecutionResult>
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
   * Returns the current persisted queries set.
   *
   * @return array<\Drupal\graphql\Plugin\PersistedQueryPluginInterface>
   */
  public function getPersistedQueryInstances(): array;

  /**
   * Returns the current persisted queries set, sorted by the plugins weight.
   *
   * @return array<\Drupal\graphql\Plugin\PersistedQueryPluginInterface>
   */
  public function getSortedPersistedQueryInstances(): array;

}
