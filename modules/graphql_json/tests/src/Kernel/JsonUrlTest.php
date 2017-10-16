<?php

namespace Drupal\Tests\graphql_json\Kernel;


use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Test json data spanning multiple urls.
 *
 * @group graphql_json
 */
class JsonUrlTest extends GraphQLFileTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql_core',
    'graphql_content',
    'graphql_json',
  ];

  /**
   * Test json data spanning multiple urls.
   */
  public function testJsonUrl() {
    $httpClient = $this->prophesize(ClientInterface::class);

    $httpClient
      ->request('GET', 'http://graphql.drupal/json')
      ->willReturn(new Response(200, [], json_encode([
        'url' => 'http://graphql.drupal/json/sub'
      ])));

    $httpClient
      ->request('GET', 'http://graphql.drupal/json/sub')
      ->willReturn(new Response(200, [], json_encode("test")));


    $this->container->set('http_client', $httpClient->reveal());

    $result = $this->executeQueryFile('url.gql', [], TRUE, TRUE);

    $this->assertEquals([
      'json' => [
        'url' => [
          'request' => [
            'json' => [
              'value' => 'test',
            ],
          ],
        ],
      ],
    ], $result['data']['route']['request']);
  }

}