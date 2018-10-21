<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

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

    $this->setUpSchema($schema, $this->getDefaultSchema());
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
    $builder = new ResolverBuilder();

    $this->mockField('node', [
      'parent' => 'Query',
    ], $builder->produce('entity_load', ['mapping' => [
      'entity_type' => $builder->fromValue('node'),
      'entity_id' => $builder->fromArgument('id'),
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
