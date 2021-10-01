<?php

namespace Drupal\graphql\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\graphql\Plugin\PersistedQueryPluginInterface;
use GraphQL\Server\OperationParams;

/**
 * Defines a GraphQL server that has configuration and executes queries.
 */
interface ServerInterface extends ConfigEntityInterface {

  /**
   * Execute an operation on this server.
   *
   * @param \GraphQL\Server\OperationParams $operation
   *
   * @return \Drupal\graphql\GraphQL\Execution\ExecutionResult
   */
  public function executeOperation(OperationParams $operation);

  /**
   * Execute multiple operations as batch on this server.
   *
   * @param \GraphQL\Server\OperationParams[] $operations
   *
   * @return \Drupal\graphql\GraphQL\Execution\ExecutionResult[]
   */
  public function executeBatch(array $operations);

  /**
   * Retrieves the server configuration.
   *
   * @return \GraphQL\Server\ServerConfig
   *   The server configuration.
   */
  public function configuration();

  /**
   * Adds a Persisted Query plugin instance to the persisted queries set.
   *
   * @param \Drupal\graphql\Plugin\PersistedQueryPluginInterface $queryPlugin
   */
  public function addPersistedQueryInstance(PersistedQueryPluginInterface $queryPlugin);

  /**
   * Removes a Persisted Query plugin instance from the persisted queries set.
   *
   * @param string $queryPluginId
   *   The plugin id to be removed.
   */
  public function removePersistedQueryInstance($queryPluginId);

  /**
   * Removes all the persisted query instances.
   */
  public function removeAllPersistedQueryInstances();

  /**
   * Returns the current persisted queries set.
   *
   * @return \Drupal\graphql\Plugin\PersistedQueryPluginInterface[]
   */
  public function getPersistedQueryInstances();

  /**
   * Returns the current persisted queries set, sorted by the plugins weight.
   *
   * @return \Drupal\graphql\Plugin\PersistedQueryPluginInterface[]
   */
  public function getSortedPersistedQueryInstances();

  /**
   * Gets disable introspection config.
   *
   * @return bool
   *   The disable introspection config, FALSE otherwise.
   */
  public function getDisableIntrospection();

  /**
   * Sets disable introspection config.
   *
   * @param bool $introspection
   *   The value for the disable introspection config.
   *
   * @return $this
   */
  public function setDisableIntrospection($introspection);

  /**
   * Gets query depth config.
   *
   * @return int|null
   *   The query depth, NULL otherwise.
   */
  public function getQueryDepth();

  /**
   * Sets query depth config.
   *
   * @param int $depth
   *   The value for the query depth config.
   *
   * @return $this
   */
  public function setQueryDepth($depth);

  /**
   * Gets query complexity config.
   *
   * @return int|null
   *   The query complexity, NULL otherwise.
   */
  public function getQueryComplexity();

  /**
   * Sets query complexity config.
   *
   * @param int $complexity
   *   The value for the query complexity config.
   *
   * @return $this
   */
  public function setQueryComplexity($complexity);

}
