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
  protected function query($query, $server = NULL, array $variables = []) {
    if (empty($server)) {
      $server = $this->test_server;
    }
    return $this->container->get('http_kernel')->handle(Request::create($server->get('endpoint'), 'GET', [
      'query' => $query,
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
  protected function batchedQueries($queries, $server = NULL) {
    if (empty($server)) {
      $server = $this->test_server;
    }
    return $this->container->get('http_kernel')->handle(Request::create($server->get('endpoint'), 'POST', [], [], [], [], json_encode($queries)));
  }

}
