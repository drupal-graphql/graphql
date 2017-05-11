<?php

namespace Drupal\Tests\graphql_core\Kernel;

use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
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
   * Test that the entity query both nodes.
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
    ], $result['data']['a'], 'Type A entities queried.');

    $this->assertEquals([
      ['uuid' => $d->uuid()],
    ], $result['data']['b'], 'Type B entity queried.');

    $this->assertEquals([
      ['uuid' => $a->uuid()],
      ['uuid' => $b->uuid()],
    ], $result['data']['limit'], 'Limit works as expected.');

    $this->assertEquals([
      ['uuid' => $b->uuid()],
      ['uuid' => $c->uuid()],
    ], $result['data']['offset'], 'Offset works as expected.');

    $this->assertEquals([
      ['uuid' => $b->uuid()],
    ], $result['data']['offset_limit'], 'Offset and limit combination works as expected.');

  }

}
