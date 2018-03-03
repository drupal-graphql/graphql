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
   * @param array $variables
   *   Query variables.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The http response object.
   */
  protected function persistedQuery($id, array $variables = []) {
    return $this->container->get('http_kernel')->handle(Request::create('/graphql', 'GET', [
      'queryId' => $id,
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
    return $this->container->get('http_kernel')->handle(Request::create('/graphql', 'POST', [], [], [], [], json_encode($queries)));
  }

}
