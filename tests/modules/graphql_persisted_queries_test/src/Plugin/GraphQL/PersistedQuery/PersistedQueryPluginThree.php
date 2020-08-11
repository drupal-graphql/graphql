<?php

namespace Drupal\graphql_persisted_queries_test\Plugin\GraphQL\PersistedQuery;

use Drupal\graphql\PersistedQuery\PersistedQueryPluginBase;
use GraphQL\Server\OperationParams;

/**
 * @PersistedQuery(
 *   id = "persisted_query_plugin_three",
 *   label = "Persisted Query Three",
 *   description = "This is the third persisted query plugin"
 * )
 *
 * Class PersistedQueryPluginThree
 * @package Drupal\graphql_persisted_queries_test\Plugin\GraphQL\PersistedQuery
 */
class PersistedQueryPluginThree extends PersistedQueryPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getQuery($id, OperationParams $operation) {
    $queryMap = $this->queryMap();
    return $queryMap[$id] ?? NULL;
  }

  /**
   * Map between persisted query IDs and corresponding GraphQL queries.
   */
  protected function queryMap() {
    return [
      'query_1' => "query { field_three { url } }",
      'query_2' => "query { field_three { url\ntitle } }",
    ];
  }

}
