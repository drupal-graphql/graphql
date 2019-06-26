<?php

namespace Drupal\graphql\Event;

use Drupal\graphql\GraphQL\Execution\QueryResult;
use Drupal\graphql\GraphQL\Execution\ServerConfig;
use GraphQL\Server\OperationParams;
use Symfony\Component\EventDispatcher\Event;

class OperationEvent extends Event {

  /**
   * Event fired before an operation is executed.
   *
   * @var string
   */
  const GRAPHQL_OPERATION_BEFORE = 'graphql.operation.before';

  /**
   * Event fired after an operation was executed.
   *
   * @var string
   */
  const GRAPHQL_OPERATION_AFTER = 'graphql.operation.after';

  /**
   * @var \GraphQL\Server\OperationParams
   */
  protected $params;

  /**
   * @var \Drupal\graphql\GraphQL\Execution\ServerConfig
   */
  protected $config;

  /**
   * @var \Drupal\graphql\GraphQL\Execution\QueryResult
   */
  protected $result;

  /**
   * OperationEvent constructor.
   *
   * @param \GraphQL\Server\OperationParams $params
   * @param \Drupal\graphql\GraphQL\Execution\ServerConfig $config
   * @param \Drupal\graphql\GraphQL\Execution\QueryResult $result
   */
  public function __construct(OperationParams $params, ServerConfig $config, QueryResult $result = NULL) {
    $this->params = $params;
    $this->config = $config;
    $this->result = $result;
  }

  /**
   * @return \Drupal\graphql\GraphQL\Execution\QueryResult
   */
  public function getResult() {
    return $this->result;
  }

  /**
   * @return \Drupal\graphql\GraphQL\Execution\ServerConfig
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * @return \GraphQL\Server\OperationParams
   */
  public function getParams() {
    return $this->params;
  }
}