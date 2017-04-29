<?php

namespace Drupal\Tests\graphql\Functional;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\simpletest\NodeCreationTrait;

/**
 * Tests some custom schema.
 *
 * @group GraphQL
 */
class NodeSchemaTest extends QueryTestBase  {
  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql_test_custom_schema', 'node'];

  /**
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  public function setUp() {
    parent::setUp();

    // Create a test content type for node testing.
    NodeType::create([
      'name' => 'article',
      'type' => 'article',
    ])->save();

    $node = Node::create([
      'type' => 'article',
      'title' => 'giraffe',
    ]);

    $node->save();
    $this->node = $node;
  }

  /**
   * @covers \Drupal\graphql\GraphQL\\EntityByIdField
   */
  public function testNodeById() {
    $nid = $this->node->id();
    $query = <<<GQL
{
  nodeById(id: $nid) {
    entityId
  }
}
    
GQL;

    $body = $this->query($query);
    $data = json_decode($body, TRUE);
    $this->assertEquals([
      'data' => [
        'nodeById' => [
          'entityId' => (string) $nid,
        ],
      ],
    ], $data);
  }
}
