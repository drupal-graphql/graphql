<?php

namespace Drupal\Tests\graphql_core\Kernel\EntityQuery;

use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test entity query support in GraphQL.
 *
 * @group graphql_core
 */
class EntityQueryTest extends GraphQLFileTestBase {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql_core',
    'node',
    'field',
    'filter',
    'text',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installConfig(['node', 'filter']);
    $this->installSchema('node', 'node_access');

    $this->createContentType(['type' => 'a']);
    $this->createContentType(['type' => 'b']);

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();
  }

  /**
   * Test that entity queries work.
   */
  public function testEntityQuery() {
    $a = $this->createNode([
      'title' => 'Node A',
      'type' => 'a',
    ]);

    $b = $this->createNode([
      'title' => 'Node B',
      'type' => 'a',
    ]);

    $c = $this->createNode([
      'title' => 'Node C',
      'type' => 'a',
    ]);

    $d = $this->createNode([
      'title' => 'Node D',
      'type' => 'b',
    ]);

    $a->save();
    $b->save();
    $c->save();
    $d->save();

    $result = $this->executeQueryFile('entity_query.gql');

    $this->assertEquals([
      ['uuid' => $a->uuid()],
      ['uuid' => $b->uuid()],
      ['uuid' => $c->uuid()],
    ], $result['data']['a']['entities'], 'Type A entities queried.');

    $this->assertEquals(3, $result['data']['a']['count'], 'Correct count is returned');

    $this->assertEquals([
      ['uuid' => $d->uuid()],
    ], $result['data']['b']['entities'], 'Type B entity queried.');

    $this->assertEquals(1, $result['data']['b']['count'], 'Correct count is returned');

    $this->assertEquals([
      ['uuid' => $a->uuid()],
      ['uuid' => $b->uuid()],
    ], $result['data']['limit']['entities'], 'Limit works as expected.');

    $this->assertEquals(3, $result['data']['limit']['count'], 'Correct count is returned');

    $this->assertEquals([
      ['uuid' => $b->uuid()],
      ['uuid' => $c->uuid()],
    ], $result['data']['offset']['entities'], 'Offset works as expected.');

    $this->assertEquals(3, $result['data']['offset']['count'], 'Correct count is returned');

    $this->assertEquals([
      ['uuid' => $b->uuid()],
    ], $result['data']['offset_limit']['entities'], 'Offset and limit combination works as expected.');

    $this->assertEquals(3, $result['data']['offset_limit']['count'], 'Correct count is returned');

    $this->assertEquals([
      ['uuid' => $a->uuid()],
      ['uuid' => $b->uuid()],
      ['uuid' => $c->uuid()],
      ['uuid' => $d->uuid()],
    ], $result['data']['all_nodes']['entities'], 'All entities queried.');

    $this->assertEquals(4, $result['data']['all_nodes']['count'], 'Correct count is returned');
  }

}
