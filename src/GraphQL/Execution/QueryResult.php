<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use GraphQL\Executor\ExecutionResult;

class QueryResult extends ExecutionResult implements RefinableCacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * QueryResult constructor.
   *
   * @param array $data
   *   Result data.
   * @param array $errors
   *   Errors collected during execution.
   * @param array $extensions
   *   User specified array of extensions.
   * @param \Drupal\Core\Cache\CacheableDependencyInterface $metadata
   *   The cache metadata collected during query execution.
   */
  public function __construct(array $data = null, array $errors = [], array $extensions = [], CacheableDependencyInterface $metadata = NULL) {
    $this->data = $data;
    $this->errors = $errors;
    $this->extensions = $extensions;

    // If no cache metadata was given, assume this result is not cacheable.
    $this->addCacheableDependency($metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    // Do not serialize the error handlers.
    return ['data', 'errors', 'extensions', 'cacheContexts', 'cacheTags', 'cacheMaxAge'];
  }
}