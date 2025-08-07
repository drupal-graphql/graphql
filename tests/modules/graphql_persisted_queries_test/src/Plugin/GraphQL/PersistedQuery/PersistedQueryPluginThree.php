<?php

declare(strict_types=1);

namespace Drupal\graphql_persisted_queries_test\Plugin\GraphQL\PersistedQuery;

use Drupal\graphql\Attribute\PersistedQuery;
use Drupal\graphql\PersistedQuery\PersistedQueryPluginBase;
use GraphQL\Server\OperationParams;

/**
 * Test persisted plugin.
 */
#[PersistedQuery(
  id: "persisted_query_plugin_three",
  label: "Persisted Query Three",
  description: "This is the third persisted query plugin"
)]
class PersistedQueryPluginThree extends PersistedQueryPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getQuery($id, OperationParams $operation): ?string {
    $queryMap = $this->queryMap();
    return $queryMap[$id] ?? NULL;
  }

  /**
   * Map between persisted query IDs and corresponding GraphQL queries.
   *
   * @return array<string, string>
   *   The query map.
   */
  protected function queryMap(): array {
    return [
      'query_1' => "query { field_three { url } }",
      'query_2' => "query { field_three { url\ntitle } }",
    ];
  }

}
