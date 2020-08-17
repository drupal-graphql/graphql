<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;

//@codingStandardsIgnoreStart
class ExecutionResult extends GraphQL\Executor\ExecutionResult implements CacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;
//@codingStandardsIgnoreEnd
}
