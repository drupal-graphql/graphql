<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\CacheableDependencyInterface;

/**
 * A single graphql query result.
 */
class QueryResult implements CacheableDependencyInterface {

  /**
   * The query result.
   *
   * @var mixed
   */
  protected $data;

  /**
   * Cache metadata collected during query execution.
   *
   * @var \Drupal\Core\Cache\CacheableDependencyInterface
   */
  protected $metadata;

  /**
   * QueryResult constructor.
   *
   * @param $data
   *   Result data.
   * @param \Drupal\Core\Cache\CacheableDependencyInterface $metadata
   *   Result metadata.
   */
  public function __construct($data, CacheableDependencyInterface $metadata) {
    $this->data = $data;
    $this->metadata = $metadata;
  }

  /**
   * Retrieve query result data.
   *
   * @return mixed
   *   The result data object.
   */
  public function getData() {
    return $this->data;
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

}