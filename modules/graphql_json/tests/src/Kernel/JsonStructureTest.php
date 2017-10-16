<?php

namespace Drupal\Tests\graphql_json\Kernel;


use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Test json data retrieval.
 *
 * @group graphql_json
 */
class JsonStructureTest extends GraphQLFileTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql_core',
    'graphql_content',
    'graphql_json',
  ];

  /**
   * Ensure that all leave types are casted into strings.
   */
  public function testJsonLeaf() {
    $httpClient = $this->prophesize(ClientInterface::class);

    $httpClient
      ->request('GET', 'http://graphql.drupal/json/string')
      ->willReturn(new Response(200, [], json_encode("test")));

    $httpClient
      ->request('GET', 'http://graphql.drupal/json/int')
      ->willReturn(new Response(200, [], json_encode(1)));

    $httpClient
      ->request('GET', 'http://graphql.drupal/json/float')
      ->willReturn(new Response(200, [], json_encode(0.5)));

    $httpClient
      ->request('GET', 'http://graphql.drupal/json/bool')
      ->willReturn(new Response(200, [], json_encode(TRUE)));

    $this->container->set('http_client', $httpClient->reveal());

    $result = $this->executeQueryFile('leaves.gql', [], TRUE, TRUE);

    $this->assertEquals([
      'data' => [
        'string' => ['request' => ['json' => ['value' => 'test']]],
        'int' => ['request' => ['json' => ['value' => '1']]],
        'float' => ['request' => ['json' => ['value' => '0.5']]],
        'bool' => ['request' => ['json' => ['value' => '1']]],
      ],
    ], $result);
  }

  /**
   * Test object traversal.
   */
  public function testJsonObject() {
    $httpClient = $this->prophesize(ClientInterface::class);

    $httpClient
      ->request('GET', 'http://graphql.drupal/json/object')
      ->willReturn(new Response(200, [], json_encode([
        'a' => 'A',
        'b' => 'B',
        'c' => 'C',
      ])));

    $this->container->set('http_client', $httpClient->reveal());

    $result = $this->executeQueryFile('object.gql', [], TRUE, TRUE);

    $this->assertEquals([
      'keys' => ['a', 'b', 'c'],
      'a' => ['value' => 'A'],
      'b' => ['value' => 'B'],
    ], $result['data']['route']['request']['json']);
  }

  public function testJsonList() {
    $httpClient = $this->prophesize(ClientInterface::class);

    $httpClient
      ->request('GET', 'http://graphql.drupal/json/list')
      ->willReturn(new Response(200, [], json_encode(['A', 'B', 'C'])));

    $this->container->set('http_client', $httpClient->reveal());

    $result = $this->executeQueryFile('list.gql', [], TRUE, TRUE);
    $this->assertEquals([
      'a' => ['value' => 'A'],
      'b' => ['value' => 'B'],
      'items' => [
        ['value' => 'A'],
        ['value' => 'B'],
        ['value' => 'C'],
      ],
    ], $result['data']['route']['request']['json']);
  }
}