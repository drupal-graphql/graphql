<?php

namespace Drupal\graphql\Event;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Executor\ExecutionResult;
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
   * @var \GraphQL\Executor\ExecutionResult
   */
  protected $result;

  /**
   * @var \Drupal\graphql\GraphQL\Execution\ResolveContext
   */
  protected $context;

  /**
   * OperationEvent constructor.
   *
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Executor\ExecutionResult $result
   */
  public function __construct(ResolveContext $context, ExecutionResult $result = NULL) {
    $this->context = $context;
    $this->result = $result;
  }

  /**
   * @return \GraphQL\Executor\ExecutionResult
   */
  public function getResult() {
    return $this->result;
  }

  /**
   * @return \Drupal\graphql\GraphQL\Execution\ResolveContext
   */
  public function getContext() {
    return $this->context;
  }

}