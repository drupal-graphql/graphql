<?php

namespace Drupal\graphql_persisted_queries_test\Plugin\GraphQL\PersistedQuery;

use Drupal\graphql\PersistedQuery\PersistedQueryPluginBase;
use GraphQL\Server\OperationParams;

/**
 * @PersistedQuery(
 *   id = "persisted_query_plugin_two",
 *   label = "Persisted Query Two",
 *   description = "This is the second persisted query plugin"
 * )
 *
 * Class PersistedQueryPluginTwo
 * @package Drupal\graphql_persisted_queries_test\Plugin\GraphQL\PersistedQuery
 */
class PersistedQueryPluginTwo extends PersistedQueryPluginBase {

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
      'query_1' => 'query { field_two }',
    ];
  }

}
