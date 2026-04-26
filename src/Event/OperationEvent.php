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
   * Event fired after an operation result was retrieved from cache.
   */
  const GRAPHQL_OPERATION_CACHE_HIT = 'graphql.operation.cache_hit';

  /**
   * OperationEvent constructor.
   *
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   Resolver context used for the query.
   * @param \GraphQL\Executor\ExecutionResult|null $result
   *   Result of the query execution.
   */
  public function __construct(
    protected ResolveContext $context,
    protected ?ExecutionResult $result = NULL,
  ) {
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
