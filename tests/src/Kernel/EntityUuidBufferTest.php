<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

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

    $this->setUpSchema($schema, $this->getDefaultSchema());
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
    $builder = new ResolverBuilder();

    $this->mockField('node', [
      'parent' => 'Query',
    ], $builder->produce('entity_load_by_uuid', ['mapping' => [
      'type' => $builder->fromValue('node'),
      'uuid' => $builder->fromArgument('uuid'),
    ]]));

    $this->mockField('title', [
      'parent' => 'Node',
    ], $builder->produce('entity_label', ['mapping' => [
      'entity' => $builder->fromParent(),
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
