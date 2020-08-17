<?php

namespace Drupal\graphql\GraphQL\Execution;

use GraphQL\Executor\ExecutionResult;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;

class ExecutionResult extends ExecutionResult implements CacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

}
