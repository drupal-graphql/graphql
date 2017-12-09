<?php

namespace Drupal\graphql\QueryProvider;

interface QueryProviderInterface {

  /**
   * Returns a query string given the query parameters.
   *
   * Can be used to load a query string from arbitrary query parameters e.g.
   * when using persisted queries (loading queries by their hash or version and
   * id).
   *
   * @param array $params
   *   The query parameters.
   *
   * @return string|null
   *   The query string or NULL if it couldn't be determined.
   */
  public function getQuery(array $params);

}
