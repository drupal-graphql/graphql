<?php

namespace Drupal\graphql\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use GraphQL\Server\OperationParams;

interface ServerInterface extends ConfigEntityInterface {

  /**
   * @param \GraphQL\Server\OperationParams $operation
   *
   * @return \Drupal\graphql\GraphQL\Execution\ExecutionResult
   */
  public function executeOperation(OperationParams $operation);

  /**
   * @param \GraphQL\Server\OperationParams[] $operations
   *
   * @return \Drupal\graphql\GraphQL\Execution\ExecutionResult[]
   */
  public function executeBatch($operations);

  /**
   * Retrieves the server configuration
   *
   * @return \GraphQL\Server\ServerConfig
   *   The server configuration.
   */
  public function configuration();
}
