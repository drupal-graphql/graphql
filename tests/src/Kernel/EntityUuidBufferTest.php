<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * @group graphql
 */
class EntityUuidBufferTest extends GraphQLTestBase {

  protected $nodeUuids = [];

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
      $node = Node::create([
        'title' => 'Node ' . $i,
        'type' => 'test',
      ]);

      $node->save();
      $this->nodeUuids[] = $node->uuid();
    }

    $schema = <<<GQL
      type Query {
        node(uuid: String): Node
      }
      type Node {
        title: String!
      }
GQL;

    $this->setUpSchema($schema);
  }

  public function testEntityUuidBuffer() {
    $query = <<<GQL
      query {
        a:node(uuid: "{$this->nodeUuids[0]}") {
          title
        }
  
        b:node(uuid: "{$this->nodeUuids[1]}") {
          title
        }
  
        c:node(uuid: "{$this->nodeUuids[2]}") {
          title
        }
      }
GQL;

    $this->mockField('node', [
      'parent' => 'Query',
    ], $this->builder->produce('entity_load_by_uuid', ['mapping' => [
      'type' => $this->builder->fromValue('node'),
      'uuid' => $this->builder->fromArgument('uuid'),
    ]]));

    $this->mockField('title', [
      'parent' => 'Node',
    ], $this->builder->produce('entity_label', ['mapping' => [
      'entity' => $this->builder->fromParent(),
    ]]));

    $metadata = $this->defaultCacheMetaData();
    $metadata->addCacheTags(['node:1', 'node:2', 'node:3']);
    $this->assertResults($query, [], [
      'a' => ['title' => 'Node 1'],
      'b' => ['title' => 'Node 2'],
      'c' => ['title' => 'Node 3'],
    ], $metadata);
  }

}
