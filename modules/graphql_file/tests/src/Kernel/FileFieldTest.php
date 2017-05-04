<?php

namespace Drupal\Tests\graphql_file\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test file attachments.
 *
 * @group graphql_file
 */
class FileFieldTest extends GraphQLFileTestBase {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'text',
    'filter',
    'file',
    'graphql_content',
    'graphql_file',
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
    $this->installSchema('file', 'file_usage');
    $this->installEntitySchema('file');
    $this->createContentType(['type' => 'test']);

    Role::load('anonymous')
      ->grantPermission('access content')
      ->save();

    EntityViewMode::create([
      'targetEntityType' => 'node',
      'id' => "node.graphql",
    ])->save();


    FieldStorageConfig::create([
      'field_name' => 'file',
      'type' => 'file',
      'entity_type' => 'node',
    ])->save();

    FieldConfig::create([
      'field_name' => 'file',
      'entity_type' => 'node',
      'bundle' => 'test',
      'label' => 'File',
    ])->save();

    EntityViewDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'test',
      'mode' => 'graphql',
      'status' => TRUE,
    ])->setComponent('file', ['type' => 'file_url_plain'])->save();
  }

  /**
   * Test a simple file field.
   */
  public function testFileField() {
    $a = $this->createNode([
      'title' => 'Node A',
      'type' => 'test',
    ]);

    $a->file->generateSampleItems(1);

    $a->save();

    $result = $this->executeQueryFile('files.gql', ['path' => '/node/' . $a->id()]);
    $file = $result['data']['route']['node']['file'];

    $this->assertEquals($a->file->entity->getSize(), $file['fileSize'], 'Retrieve correct file size.');
    $this->assertEquals($a->file->entity->getMimeType(), $file['mimeType'], 'Retrieve correct mime type.');
    $this->assertEquals($a->file->entity->url(), $file['url'], 'Retrieve correct file path.');
  }

}
