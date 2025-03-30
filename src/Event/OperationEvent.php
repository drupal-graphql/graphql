<?php

declare(strict_types=1);

namespace Drupal\graphql\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Executor\ExecutionResult;

/**
 * Represents an event that is triggered before and after a GraphQL operation.
 */
class OperationEvent extends Event {

  /**
   * Event fired before an operation is executed.
   */
  const GRAPHQL_OPERATION_BEFORE = 'graphql.operation.before';

  /**
   * Event fired after an operation was executed.
   */
  const GRAPHQL_OPERATION_AFTER = 'graphql.operation.after';

  /**
   * Result of the query execution.
   */
  protected ?ExecutionResult $result;

  /**
   * Resolver context used for the query.
   */
  protected ResolveContext $context;

  /**
   * OperationEvent constructor.
   */
  public function __construct(ResolveContext $context, ?ExecutionResult $result = NULL) {
    $this->context = $context;
    $this->result = $result;
  }

  /**
   * Returns the execution result.
   */
  public function getResult(): ?ExecutionResult {
    return $this->result;
  }

  /**
   * Returns the resolver context.
   */
  public function getContext(): ResolveContext {
    return $this->context;
  }

}
