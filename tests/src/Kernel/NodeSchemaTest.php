<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\node\Entity\Node;

/**
 * Tests some custom schema.
 *
 * @group graphql
 */
class NodeSchemaTest extends QueryTestBase  {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql', 'graphql_test_custom_schema', 'node', 'user', 'system'];

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

    $query = <<<GQL
{
  nodeById(id: $nid) {
    nodeId
  }
}
    
GQL;

    $response = $this->query($query);
    $data = json_decode($response->getContent(), TRUE);
    $this->assertEquals([
      'data' => [
        'nodeById' => [
          'nodeId' => $nid,
        ],
      ],
    ], $data);

    $tags = explode(' ', $response->headers->get('X-Drupal-Cache-Tags', NULL));
    $this->assertContains('config:user.role.anonymous', $tags, "Cache is tagged for anonymous user.");
    $this->assertContains("node:$nid", $tags, "Cache is tagged for the correct node id.");
  }

}
