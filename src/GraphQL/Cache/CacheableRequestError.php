<?php

namespace Drupal\graphql\GraphQL\Cache;

use GraphQL\Server\RequestError;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;

/**
 * Cacheable Request Error.
 */
class CacheableRequestError extends RequestError implements CacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;
}
