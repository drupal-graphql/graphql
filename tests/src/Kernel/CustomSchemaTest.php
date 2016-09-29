<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\node\Entity\Node;

/**
 * Tests some custom schema.
 */
class CustomSchemaTest extends QueryTestBase  {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql_test_custom_schema'];

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);

    $container->setParameter('http.response.debug_cacheability_headers', TRUE);
  }

  public function testSchemaWithCaching() {
    $node = Node::create([
      'type' => 'article',
      'title' => 'giraffe',
    ]);
    $node->save();
    $nid = $node->id();
    $uuid = $node->uuid();

    $query = <<<GQL
{
  nodeByUuid(uuid: "$uuid") {
    entityId
  }
}
    
GQL;

    $response = $this->query($query);
    $data = json_decode($response->getContent(), TRUE);
    $this->assertEquals([
      'data' => [
        'nodeByUuid' => [
          'entityId' => $nid,
        ],
      ],
    ], $data);

    $this->assertEquals('config:user.role.anonymous node:1', $response->headers->get('X-Drupal-Cache-Tags', NULL));
  }

}
