<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\graphql\Entity\Server;
use Drupal\graphql\Entity\ServerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test trait for the GraphQL HTTP interface.
 */
trait HttpRequestTrait {

  /**
   * The default server.
   *
   * @var \Drupal\graphql\Entity\Server
   */
  protected $server;

  /**
   * Issue a simple query over http.
   *
   * @param string $query
   *   The query string.
   * @param \Drupal\graphql\Entity\Server|null $server
   *   The server instance.
   * @param array $variables
   *   Query variables.
   * @param array|null $extensions
   *   The query extensions.
   * @param bool $persisted
   *   Flag if the query is actually the identifier of a persisted query.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The http response object.
   */
  protected function query($query, $server = NULL, array $variables = [], array $extensions = NULL, $persisted = FALSE) {
    $server = $server ?: $this->server;
    if (!($server instanceof Server)) {
      throw new \LogicException('Invalid server.');
    }

    $endpoint = $this->server->get('endpoint');
    $extensions = !empty($extensions) ? ['extensions' => $extensions] : [];
    // If the persisted flag is true, then instead of sending the full query to
    // the server we only send the query id.
    $query_key = $persisted ? 'queryId' : 'query';
    $request = Request::create($endpoint, 'GET', [
      $query_key => $query,
      'variables' => $variables,
    ] + $extensions);

    return $this->container->get('http_kernel')->handle($request);
  }

  /**
   * Simulate batched queries over http.
   *
   * @param array[] $queries
   *   A set of queries to be executed in one go.
   * @param \Drupal\graphql\Entity\ServerInterface $server
   *   The server instance.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The http response object.
   */
  protected function batchedQueries(array $queries, ServerInterface $server = NULL) {
    $server = $server ?: $this->server;
    if (!($server instanceof Server)) {
      throw new \LogicException('Invalid server.');
    }

    $queries = json_encode($queries);
    $endpoint = $this->server->get('endpoint');
    $request = Request::create($endpoint, 'POST', [], [], [], [], $queries);
    return $this->container->get('http_kernel')->handle($request);
  }

}
