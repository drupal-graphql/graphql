<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use GraphQL\Executor\ExecutionResult;

class QueryResult extends ExecutionResult implements CacheableDependencyInterface {

  /**
   * Cache metadata collected during query execution.
   *
   * @var \Drupal\Core\Cache\CacheableDependencyInterface
   */
  protected $metadata;

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
    $this->metadata = $metadata ?: (new CacheableMetadata())->setCacheMaxAge(0);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return $this->metadata->getCacheContexts();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return $this->metadata->getCacheTags();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return $this->metadata->getCacheMaxAge();
  }

  /**
   * Don't serialize this object.
   *
   * Drop any serialization, since this will break php unit because the
   * backtrace contains this object and tries to serialize a closure thats
   * hidden deep in webonyx.
   *
   * // TODO: Solve me differently.
   *
   * @return string[]
   *   The properties to serialize.
   */
  public function __sleep() {
    return [];
  }
}