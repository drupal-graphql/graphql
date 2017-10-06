<?php

namespace Drupal\Tests\graphql_json\Kernel;


use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Test loading entities from json.
 *
 * @group graphql_json
 */
class JsonEntityTest extends GraphQLFileTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'graphql_content',
    'graphql_json',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();
  }

  /**
   * Test loading entities from json.
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
      'status' => 1,
    ]));
    $this->container->set('entity.repository', $entityRepository->reveal());


    $result = $this->executeQueryFile('entity.gql', [], TRUE, TRUE);

    $this->assertEquals([
      'json' => [
        'node' => [
          'uuid' => 'abc',
        ],
      ],
    ], $result['data']['route']['request']);
  }

}