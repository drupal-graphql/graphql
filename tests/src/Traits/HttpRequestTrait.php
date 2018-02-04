<?php

namespace Drupal\Tests\graphql\Traits;

use Symfony\Component\HttpFoundation\Request;

/**
 * Test trait for the GraphQL HTTP interface.
 */
trait HttpRequestTrait {

  /**
   * Issue a simple query over http.
   *
   * @param string $query
   *   The query string.
   * @param array $variables
   *   Query variables.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The http response object.
   */
  protected function query($query, array $variables = []) {
    return $this->container->get('http_kernel')->handle(Request::create('/graphql', 'GET', [
      'query' => $query,
      'variables' => $variables,
    ]));
  }

  /**
   * Issue a persisted query over http.
   *
   * @param $id
   *   The query id.
   * @param $version
   *   The query map version.
   * @param array $variables
   *   Query variables.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The http response object.
   */
  protected function persistedQuery($id, $version, array $variables = []) {
    return $this->container->get('http_kernel')->handle(Request::create('/graphql', 'GET', [
      'id' => $id,
      'version' => $version,
      'variables' => $variables,
    ]));
  }

  /**
   * Simulate batched queries over http.
   *
   * @param string[] $queries
   *   A set of queries to be executed in one go.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The http response object.
   */
  protected function batchedQueries($queries) {
    return $this->container->get('http_kernel')->handle(Request::create('/graphql', 'GET', [], [], [], [], json_encode($queries)));
  }

}
