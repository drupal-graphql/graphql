<?php

namespace Drupal\graphql\GraphQL\QueryProvider;

use GraphQL\Server\OperationParams;

interface QueryProviderInterface {

  /**
   * Returns a query string given the query parameters.
   *
   * Can be used to load a query string from arbitrary query parameters e.g.
   * when using persisted queries (loading queries by their hash or version and
   * id).
   *
   * @param string $id
   *   The query id.
   * @param \GraphQL\Server\OperationParams $operation
   *   The operation parameters.
   *
   * @return string|null
   *   The query string or NULL if it couldn't be determined.
   */
  public function getQuery($id, OperationParams $operation);

}
