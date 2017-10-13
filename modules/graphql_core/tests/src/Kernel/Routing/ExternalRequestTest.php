<?php

namespace Drupal\Tests\graphql_core\Kernel\Routing;

use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Test external requests.
 *
 * @group graphql_core
 */
class ExternalRequestTest extends GraphQLFileTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql_core'];

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

    $result = $this->executeQueryFile('external_requests.gql', [], TRUE, TRUE);

    $this->assertEquals(200, $result['data']['route']['request']['code']);
    $this->assertContains('<p>GraphQL is awesome!</p>', $result['data']['route']['request']['content']);
    $this->assertEquals('test', $result['data']['route']['request']['header']);
  }

}
