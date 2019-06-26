<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * @group graphql
 */
class EntityBufferTest extends GraphQLTestBase {

  protected $nodeIds = [];

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityBuffer;

  protected function setUp() {
    parent::setUp();

    NodeType::create([
      'type' => 'test',
      'name' => 'Test',
    ])->save();

    foreach (range(1, 3) as $i) {
      $this->nodeIds[] = Node::create([
        'title' => 'Node ' . $i,
        'type' => 'test',
      ])->save();
    }

    $schema = <<<GQL
      type Query {
        node(id: String): Node
      }
      
      type Node {
        title: String!
      }
GQL;

    $this->setUpSchema($schema);
  }

  public function testEntityBuffer() {
    $query = <<<GQL
      query {
        a:node(id: "1") {
          title
        }
  
        b:node(id: "2") {
          title
        }
  
        c:node(id: "3") {
          title
        }
      }
GQL;

    $this->mockResolver('Query', 'node',
      $this->builder->produce('entity_load')
        ->map('type', $this->builder->fromValue('node'))
        ->map('id', $this->builder->fromArgument('id'))
    );

    $this->mockResolver('Node', 'title',
      $this->builder->produce('entity_label')
        ->map('entity', $this->builder->fromParent())
    );

    $metadata = $this->defaultCacheMetaData();
    $metadata->addCacheTags(['node:1', 'node:2', 'node:3']);
    $this->assertResults($query, [], [
      'a' => ['title' => 'Node 1'],
      'b' => ['title' => 'Node 2'],
      'c' => ['title' => 'Node 3'],
    ], $metadata);
  }

}
