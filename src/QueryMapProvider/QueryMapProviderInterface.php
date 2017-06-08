<?php

namespace Drupal\graphql\QueryMapProvider;

interface QueryMapProviderInterface {

  /**
   * Returns a query string given a query map version and query id.
   *
   * @param string $version
   *   The version of the query map.
   * @param string $id
   *   The id of the query in the query map.
   * @return string|null
   *   The query string or NULL if it doesn't exist.
   */
  public function getQuery($version, $id);

}
