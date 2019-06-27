<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\RefinableCacheableDependencyTrait;

class ExecutionResult extends \GraphQL\Executor\ExecutionResult {
  use RefinableCacheableDependencyTrait;

}
