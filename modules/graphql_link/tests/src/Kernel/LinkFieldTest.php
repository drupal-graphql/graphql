<?php

namespace Drupal\Tests\graphql_link\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test entity reference traversal in GraphQL.
 *
 * @group graphql_link
 */
class LinkFieldTest extends GraphQLFileTestBase {
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
    'link',
    'graphql_content',
    'graphql_link',
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

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();

    FieldStorageConfig::create([
      'field_name' => 'links',
      'type' => 'link',
      'entity_type' => 'node',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ])->save();

    FieldConfig::create([
      'field_name' => 'links',
      'entity_type' => 'node',
      'bundle' => 'test',
      'label' => 'Links',
    ])->save();

    EntityViewMode::create([
      'targetEntityType' => 'node',
      'id' => "node.graphql",
    ])->save();

    EntityViewDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'test',
      'mode' => 'graphql',
      'status' => TRUE,
    ])->setComponent('links', ['type' => 'link'])->save();

  }

  /**
   * Test two mutually connected nodes.
   */
  public function testLinksField() {
    $a = $this->createNode([
      'title' => 'Node A',
      'type' => 'test',
    ]);

    $a->links = [
      ['title' => 'Internal link', 'uri' => 'internal:/node/1'],
      ['title' => 'External link', 'uri' => 'http://drupal.org'],
    ];

    $a->save();

    $result = $this->executeQueryFile('link.gql', ['path' => '/node/' . $a->id()]);

    $this->assertEquals([
      'links' => [
        [
          'label' => 'Internal link',
          'route' => [
            'internalPath' => '/node/1',
            'aliasedPath' => '/node/1',
            'isRouted' => TRUE,
          ],
        ],
        [
          'label' => 'External link',
          'route' => [
            'internalPath' => 'http://drupal.org',
            'aliasedPath' => 'http://drupal.org',
            'isRouted' => FALSE,
          ],
        ],
      ],
    ], $result['data']['route']['node'], 'Circular reference resolved properly');
  }

}
