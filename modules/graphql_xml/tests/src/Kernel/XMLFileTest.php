<?php

namespace Drupal\Tests\graphql_xml\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql\Traits\GraphQLFileTestTrait;
use Drupal\user\Entity\Role;

/**
 * Test xml graphql fields.
 *
 * @group graphql_xml
 */
class XMLFileTest extends KernelTestBase {
  use NodeCreationTrait;
  use GraphQLFileTestTrait;

  public static $modules = [
    'system',
    'path',
    'field',
    'text',
    'filter',
    'file',
    'graphql_file',
    'node',
    'user',
    'graphql',
    'graphql_test',
    'graphql_core',
    'graphql_content',
    'graphql_file',
    'graphql_xml',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('user');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('file', 'file_usage');
    $this->installEntitySchema('file');

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();

    NodeType::create([
      'name' => 'graphql',
      'type' => 'graphql',
    ])->save();

    FieldStorageConfig::create([
      'field_name' => 'file',
      'type' => 'file',
      'entity_type' => 'node',
    ])->save();

    FieldConfig::create([
      'field_name' => 'file',
      'entity_type' => 'node',
      'bundle' => 'graphql',
      'label' => 'File',
    ])->save();

    EntityViewMode::create([
      'targetEntityType' => 'node',
      'id' => "node.graphql",
    ])->save();

    EntityViewDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'graphql',
      'mode' => 'graphql',
      'status' => TRUE,
    ])
      ->setComponent('file', ['type' => 'graphql_file'])
      ->save();

    $this->container->get('config.factory')->getEditable('graphql_content.schema')
      ->set('types', [
        'node' => [
          'exposed' => TRUE,
          'bundles' => [
            'graphql' => [
              'exposed' => TRUE,
              'view_mode' => 'node.graphql',
            ],
          ],
        ],
        'file' => [
          'exposed' => TRUE,
          'bundles' => [
            'file' => [
              'exposed' => TRUE,
            ],
          ],
        ],
      ])
      ->save();
  }

  /**
   * Test json file fields.
   */
  public function testXMLFileField() {
    $file = File::create([
      'uri' => drupal_get_path('module', 'graphql_xml') . '/tests/files/test.xml',
    ]);
    $file->save();
    $entity = Node::create([
      'type' => 'graphql',
      'title' => 'XML file field test',
      'file' => $file,
    ]);
    $entity->save();

    $result = $this->executeQueryFile('file.gql', [
      'path' => '/node/' . $entity->id(),
    ], TRUE, TRUE);

    $this->assertEquals([
      'xml' => [
        'xpath' => [
          ['content' => 'Test'],
        ],
      ],
    ], $result['data']['route']['entity']['file']);
  }

}
