<?php

namespace Drupal\graphql\GraphQL\Execution;

use GraphQL\Deferred;
use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Utils\Utils;

class ContextualizedPromiseAdapter extends SyncPromiseAdapter {

  /**
   * @var \Drupal\graphql\GraphQL\Execution\ResolveContext
   */
  protected $context;

  public function __construct(ResolveContext $context) {
    $this->context = $context;
  }

  /**
   * @inheritdoc
   */
  public function convertThenable($thenable)
  {
    if (!$thenable instanceof Deferred) {
      throw new InvariantViolation('Expected instance of GraphQL\Deferred, got ' . Utils::printSafe($thenable));
    }
    return new Promise($thenable->promise, $this);
  }

  /**
   * @inheritdoc
   */
  public function then(Promise $promise, callable $onFulfilled = null, callable $onRejected = null)
  {
    /** @var SyncPromise $promise */
    $promise = $promise->adoptedPromise;
    return new Promise($promise->then($onFulfilled, $onRejected), $this);
  }
}
