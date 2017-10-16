<?php

namespace Drupal\Tests\graphql_xml\Kernel;

use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Test xml data from urls.
 *
 * @group graphql_xml
 */
class XMLUrlTest extends GraphQLFileTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql_core',
    'graphql_content',
    'graphql_xml',
  ];

  /**
   * Test xml response.
   */
  public function testXMLResponse() {
    $httpClient = $this->prophesize(ClientInterface::class);

    $httpClient
      ->request('GET', 'http://graphql.drupal/xml')
      ->willReturn(new Response(200, [], '<test><a>Test</a></test>'));

    $this->container->set('http_client', $httpClient->reveal());

    $result = $this->executeQueryFile('url.gql', [], TRUE, TRUE);

    $this->assertEquals([
      'xml' => [
        'xpath' => [
          ['content' => 'Test'],
        ],
      ],
    ], $result['data']['route']['request']);
  }

  /**
   * Test nested xml responses.
   */
  public function testNestedXMLResponse() {
    $httpClient = $this->prophesize(ClientInterface::class);

    $httpClient
      ->request('GET', 'http://graphql.drupal/xml')
      ->willReturn(new Response(200, [], '<test><a href="http://graphql.drupal/xml/sub">Test</a></test>'));

    $httpClient
      ->request('GET', 'http://graphql.drupal/xml/sub')
      ->willReturn(new Response(200, [], '<sub>Subtest</sub>'));

    $this->container->set('http_client', $httpClient->reveal());

    $result = $this->executeQueryFile('nested_url.gql', [], TRUE, TRUE);

    $this->assertEquals([
      'xml' => [
        'url' => [
          [
            'request' => [
              'xml' => [
                'content' => 'Subtest',
              ],
            ],
          ],
        ],
      ],
    ], $result['data']['route']['request']);
  }

}
