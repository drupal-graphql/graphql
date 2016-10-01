<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\node\Entity\Node;

/**
 * Tests some custom schema.
 *
 * @group GraphQL
 */
class NodeSchemaTest extends QueryTestBase  {

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

    $this->assertEquals("config:user.role.anonymous node:$nid", $response->headers->get('X-Drupal-Cache-Tags', NULL));
  }

}
