<?php

namespace Drupal\Tests\graphql_entity_reference\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test entity reference traversal in GraphQL.
 *
 * @group graphql_entity_reference
 */
class EntityReferenceTest extends GraphQLFileTestBase {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;
  use EntityReferenceTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'text',
    'filter',
    'entity_reference',
    'graphql_content',
    'graphql_entity_reference',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('node');
    $this->installConfig('filter');
    $this->installEntitySchema('node');
    $this->installSchema('node', 'node_access');
    $this->createContentType(['type' => 'test']);
    $this->createEntityReferenceField('node', 'test', 'related', 'Related', 'node');

    EntityViewMode::create([
      'targetEntityType' => 'node',
      'id' => "node.graphql",
    ])->save();

    EntityViewDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'test',
      'mode' => 'graphql',
      'status' => TRUE,
    ])->save();

    entity_get_display('node', 'test', 'graphql')
      ->setComponent('related', ['type' => 'entity_reference_entity_view'])
      ->save();

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();
  }

  /**
   * Test two mutually connected nodes.
   */
  public function testEntityReference() {
    $a = $this->createNode([
      'title' => 'Node A',
      'type' => 'test',
    ]);

    $b = $this->createNode([
      'title' => 'Node B',
      'type' => 'test',
    ]);

    $a->related = $b;
    $b->related = $a;

    $a->save();
    $b->save();

    $result = $this->executeQueryFile('related.gql', ['path' => '/node/' . $a->id()]);

    $this->assertEquals([
      'entityLabel' => 'Node A',
      'related' => [
        'entityLabel' => 'Node B',
        'related' => [
          'entityLabel' => 'Node A',
        ],
      ],
    ], $result['data']['route']['node'], 'Circular reference resolved properly');
  }

  /**
   * Test empty references.
   */
  public function testEmptyEntityReference() {
    $a = $this->createNode([
      'title' => 'Node A',
      'type' => 'test',
    ]);

    $a->save();

    $result = $this->executeQueryFile('related.gql', ['path' => '/node/' . $a->id()]);

    $this->assertEquals([
      'entityLabel' => 'Node A',
      'related' => NULL,
    ], $result['data']['route']['node'], 'Circular reference resolved properly');
  }

}
