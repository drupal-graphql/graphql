<?php

namespace Drupal\graphql\Cache;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\Execution\QueryResult;

class CacheableQueryResponse extends CacheableJsonResponse {

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

    // Extract the cacheability metadata from the query result object.
    $this->cacheabilityMetadata = new CacheableMetadata();
    $this->cacheabilityMetadata->addCacheableDependency($data);
  }

}