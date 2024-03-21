<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Component\Serialization\Json;
use GuzzleHttp\RequestOptions;

/**
 * Helper trait for GraphQL functional tests.
 */
trait GraphQLFunctionalTestsTrait {

  /**
   * Send an APQ request.
   *
   * @param string $endpoint
   *   The server endpoint.
   * @param string $query
   *   The GraphQl query to execute.
   * @param string $variables
   *   The variables for the query.
   * @param bool $withQuery
   *   Whether to request with query parameter.
   *
   * @return array
   *   The response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function apqRequest(string $endpoint, string $query, string $variables = '', bool $withQuery = FALSE): array {
    $hash = hash('sha256', $query);
    $extensions = '{"persistedQuery":{"version":1,"sha256Hash":"' . $hash . '"}}';

    $requestOptions = [];
    $requestOptions[RequestOptions::QUERY]['extensions'] = $extensions;

    if ($variables !== '') {
      $requestOptions[RequestOptions::QUERY]['variables'] = $variables;
    }
    if ($withQuery) {
      $requestOptions[RequestOptions::QUERY]['query'] = $query;
    }

    /** @var \GuzzleHttp\Client $client */
    $client = $this->container->get('http_client_factory')->fromOptions([
      'timeout' => NULL,
      'verify' => FALSE,
    ]);

    $response = $client->request('GET', $this->getAbsoluteUrl($endpoint), $requestOptions);

    return Json::decode($response->getBody()->getContents());
  }

  /**
   * Creates an APQ request for a given query that is expected not to be found.
   *
   * @param string $query
   *   The query to send.
   * @param string $variables
   *   The variables for the query.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function assertPersistedQueryNotFound(string $query, string $variables = ''): void {
    $response = $this->apqRequest(
      $this->server->endpoint,
      $query,
      $variables
    );
    $this->assertEquals(
      'PersistedQueryNotFound',
      $response['errors'][0]['message']
    );
  }

}
