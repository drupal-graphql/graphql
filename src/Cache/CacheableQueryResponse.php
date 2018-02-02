<?php

namespace Drupal\graphql\Cache;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\Execution\QueryResult;

class CacheableQueryResponse extends CacheableJsonResponse {

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
   * CacheableQueryResponse constructor.
   *
   * @param \Drupal\graphql\GraphQL\Execution\QueryResult $data
   *   The graphql query result object.
   * @param int $status
   *   The http status code of the response.
   * @param array $headers
   *   The http headers of the response.
   */
  public function __construct(QueryResult $data, $status = 200, array $headers = []) {
    parent::__construct($data->getData(), $status, $headers);

    $this->responseMetadata = $data->getResponseMetadata();
    $this->schemaResponseMetadata = $data->getSchemaResponseMetadata();

    // Extract the cacheability metadata from the query result object.
    $this->cacheabilityMetadata = new CacheableMetadata();
    $this->cacheabilityMetadata->addCacheableDependency($data);
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