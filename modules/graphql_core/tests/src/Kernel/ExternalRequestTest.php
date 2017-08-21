<?php

namespace Drupal\Tests\graphql_core\Kernel;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Test external requests.
 */
class ExternalRequestTest extends GraphQLFileTestBase {

  /**
   * Test external requests.
   */
  public function testExternalRequests() {

    $client = $this->prophesize(ClientInterface::class);
    $client->request('GET', 'http://drupal.graphql')->willReturn(new Response(
      200,
      ['graphql' => 'test'],
      '<p>GraphQL is awesome!</p>'
    ));

    $this->container->set('http_client', $client->reveal());

    $result = $this->executeQueryFile('external_requests.gql');

    $this->assertEquals(200, $result['data']['route']['request']['code']);
    $this->assertContains('<p>GraphQL is awesome!</p>', $result['data']['route']['request']['content']);
    $this->assertEquals('test', $result['data']['route']['request']['header']);
  }

}
