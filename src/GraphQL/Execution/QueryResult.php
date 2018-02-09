<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;

class QueryResult implements CacheableDependencyInterface {

  /**
   * The query result.
   *
   * @var mixed
   */
  protected $data;

  /**
   * Merged cache metadata from the response and the schema.
   *
   * @var \Drupal\Core\Cache\CacheableDependencyInterface
   */
  protected $metadata;

  /**
   * Cache metadata collected during query execution.
   *
   * @var \Drupal\Core\Cache\CacheableDependencyInterface
   */
  protected $responseMetadata;

  /**
   * Static response cache metadata from the schema.
   *
   * @var \Drupal\Core\Cache\CacheableDependencyInterface
   */
  protected $schemaResponseMetadata;

  /**
   * QueryResult constructor.
   *
   * @param $data
   *   Result data.
   * @param \Drupal\Core\Cache\CacheableDependencyInterface $responseMetadata
   *   The cache metadata collected during query execution.
   * @param \Drupal\Core\Cache\CacheableDependencyInterface $schemaResponseMetadata
   *   The schema's response cache metadata.
   */
  public function __construct($data, CacheableDependencyInterface $responseMetadata, CacheableDependencyInterface $schemaResponseMetadata) {
    $this->data = $data;
    $this->responseMetadata = $responseMetadata;
    $this->schemaResponseMetadata = $schemaResponseMetadata;

    $this->metadata = new CacheableMetadata();
    $this->metadata->addCacheableDependency($responseMetadata);
    $this->metadata->addCacheableDependency($schemaResponseMetadata);
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

  /**
   * Gets the response cache metadata.
   *
   * @return \Drupal\Core\Cache\CacheableDependencyInterface
   *   The response cache metadata.
   */
  public function getResponseMetadata() {
    return $this->responseMetadata;
  }

  /**
   * Gets the schema's response cache metadata.
   *
   * @return \Drupal\Core\Cache\CacheableDependencyInterface
   *   The schema's response cache metadata.
   */
  public function getSchemaResponseMetadata() {
    return $this->schemaResponseMetadata;
  }

}