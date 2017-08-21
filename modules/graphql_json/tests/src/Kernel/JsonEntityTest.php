<?php

namespace Drupal\Tests\graphql_json\Kernel;


use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\node\Entity\Node;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Test json data spanning multiple urls.
 */
class JsonEntityTest extends GraphQLFileTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'graphql_json',
  ];

  /**
   * Test json data spanning multiple urls.
   */
  public function testJsonEntity() {
    $httpClient = $this->prophesize(ClientInterface::class);
    $httpClient
      ->request('GET', 'http://graphql.drupal/json')
      ->willReturn(new Response(200, [], json_encode([
        'node' => 'abc',
      ])));
    $this->container->set('http_client', $httpClient->reveal());

    $entityRepository = $this->prophesize(EntityRepositoryInterface::class);
    $entityRepository->loadEntityByUuid('node', 'abc')->willReturn(Node::create([
      'uuid' => 'abc',
      'type' => 'article',
    ]));
    $this->container->set('entity.repository', $entityRepository->reveal());


    $result = $this->executeQueryFile('entity.gql');

    $this->assertEquals([
      'json' => [
        'node' => [
          'uuid' => 'abc',
        ],
      ],
    ], $result['data']['route']['request']);
  }

}