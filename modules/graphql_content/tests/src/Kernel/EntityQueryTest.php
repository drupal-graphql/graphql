<?php

namespace Drupal\Tests\graphql_content\Kernel;

use Drupal\node\NodeInterface;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test entity query support in GraphQL.
 *
 * @group graphql_content
 */
class EntityQueryTest extends GraphQLFileTestBase {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'filter',
    'text',
    'graphql_content',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installConfig(['node', 'filter']);
    $this->installSchema('node', 'node_access');
    $this->createContentType(['type' => 'test']);

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();
  }

  /**
   * Creates a handful of nodes for testing.
   *
   * @return \Drupal\node\NodeInterface[]
   *   An array of nodes keyed by their ids.
   */
  protected function createNodes() {
    $a = $this->createNode([
      'title' => 'Node A',
      'type' => 'test',
    ]);

    $b = $this->createNode([
      'title' => 'Node B',
      'type' => 'test',
    ]);

    $c = $this->createNode([
      'title' => 'Node C',
      'type' => 'test',
    ]);

    $d = $this->createNode([
      'title' => 'Node D',
      'type' => 'test',
    ]);

    $a->save();
    $b->save();
    $c->save();
    $d->save();

    return [$a, $b, $c, $d];
  }

  /**
   * Test that the entity query returns all nodes if no args are given.
   */
  public function testEntityQueryWithoutArguments() {
    $nodes = $this->createNodes();
    $expected = array_values(array_map(function (NodeInterface $node) {
      return ['entityLabel' => $node->label()];
    }, $nodes));

    $result = $this->executeQueryFile('entity_query_no_args.gql');
    $this->assertEquals($expected, $result['data']['allNodes']['entities']);
  }

  /**
   * Test that the entity query returns all published nodes.
   */
  public function testEntityQueryWithArguments() {
    $nodes = $this->createNodes();
    $node = reset($nodes);

    $expected = [['entityLabel' => $node->label()]];
    $result = $this->executeQueryFile('entity_query_args.gql', ['id' => $node->id()]);
    $this->assertEquals($expected, $result['data']['onlyNodeWithId']['entities']);
  }

  /**
   * Test that the entity query returns nodes with pagination.
   */
  public function testEntityQueryWithOffset() {
    $nodes = $this->createNodes();
    $expected = array_values(array_map(function (NodeInterface $node) {
      return ['entityLabel' => $node->label()];
    }, array_slice($nodes, 1)));

    $result = $this->executeQueryFile('entity_query_offset.gql');
    $this->assertEquals($expected, $result['data']['allNodesExceptFirst']['entities']);
  }
}
