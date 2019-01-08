<?php

namespace Drupal\graphql_query_provider_test;

use Drupal\graphql\GraphQL\QueryProvider\QueryProviderInterface;
use GraphQL\Server\OperationParams;

/**
 * A dummy graphql query provider with a hardcoded query map.
 */
class DummyQueryProvider implements QueryProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function getQuery($id, OperationParams $operation) {
    $queryMap = $this->queryMap();
    return $queryMap[$id];
  }

  protected function queryMap() {
    return[
      'query_1' => 'query { field_one }',
      'query_2' => 'query { field_two }',
    ];
  }
}
