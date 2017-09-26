<?php

namespace Drupal\Tests\graphql\Traits;

use Symfony\Component\HttpFoundation\Request;

/**
 * Common methods for GraphQL query tests.
 */
trait QueryTrait {

  /**
   * Issue a simple query without caring about the result.
   *
   * @param $query
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
   * Issue a persisted query.
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
   * Simulate batched queries.
   *
   * @param $queries
   *   A set of queries to be executed in one go.
   * @return \Symfony\Component\HttpFoundation\Response
   *   The http response object.
   */
  protected function batchedQueries($queries) {
    return $this->container->get('http_kernel')->handle(Request::create('/graphql', 'GET', [], [], [], [], json_encode($queries)));
  }

}
