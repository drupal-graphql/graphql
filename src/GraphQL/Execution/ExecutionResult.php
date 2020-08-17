<?php

namespace Drupal\graphql\GraphQL\Execution;

use GraphQL\Executor\ExecutionResult as LibraryExecutionResult;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;

class ExecutionResult extends LibraryExecutionResult implements CacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

}
