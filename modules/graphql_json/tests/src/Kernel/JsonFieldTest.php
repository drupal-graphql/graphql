<?php

namespace Drupal\Tests\graphql_json\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\entity_test\Entity\EntityTestBundle;
use Drupal\entity_test\Entity\EntityTestWithBundle;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_core\Traits\GraphQLFileTestTrait;
use Drupal\user\Entity\Role;

/**
 * Test json graphql fields.
 *
 * @group graphql_xml
 */
class JsonFieldTest extends KernelTestBase {
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
    'graphql_core',
    'graphql_content',
    'graphql_file',
    'graphql_json',
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
      'field_name' => 'json',
      'type' => 'text_long',
      'entity_type' => 'node',
    ])->save();

    FieldConfig::create([
      'field_name' => 'json',
      'entity_type' => 'node',
      'bundle' => 'graphql',
      'label' => 'Json',
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
      ->setComponent('json', ['type' => 'graphql_json'])
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
   * Test json text fields.
   */
  public function testJsonTextField() {
    $entity = Node::create([
      'type' => 'graphql',
      'title' => 'Json text field test',
      'json' => '{"test":"test"}',
    ]);
    $entity->save();

    $result = $this->executeQueryFile('field_text.gql', [
      'path' => '/node/' . $entity->id(),
    ], TRUE, TRUE);

    $this->assertEquals([
      'path' => [
        'value' => 'test',
      ],
    ], $result['data']['route']['entity']['json']);
  }

  /**
   * Test json file fields.
   */
  public function testJsonFileField() {
    $file = File::create([
      'uri' => drupal_get_path('module', 'graphql_json') . '/tests/files/test.json',
    ]);
    $file->save();
    $entity = Node::create([
      'type' => 'graphql',
      'title' => 'Json text field test',
      'file' => $file,
    ]);
    $entity->save();

    $result = $this->executeQueryFile('field_file.gql', [
      'path' => '/node/' . $entity->id(),
    ], TRUE, TRUE);

    $this->assertEquals([
      'path' => [
        'value' => 'test',
      ],
    ], $result['data']['route']['entity']['file']['json']);
  }

}
