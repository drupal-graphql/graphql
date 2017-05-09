<?php

namespace Drupal\Tests\graphql_image\Kernel;

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
 * @group graphql_image
 */
class ImageFieldTest extends GraphQLFileTestBase {
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
    'image',
    'graphql_content',
    'graphql_file',
    'graphql_image',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('node');
    $this->installConfig('filter');
    $this->installConfig('image');
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
      'field_name' => 'image',
      'type' => 'image',
      'entity_type' => 'node',
    ])->save();

    FieldConfig::create([
      'field_name' => 'image',
      'entity_type' => 'node',
      'bundle' => 'test',
      'label' => 'Image',
    ])->save();

    EntityViewDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'test',
      'mode' => 'graphql',
      'status' => TRUE,
    ])->setComponent('image', ['type' => 'image'])->save();
  }

  /**
   * Test a simple file field.
   */
  public function testImageField() {
    $a = $this->createNode([
      'title' => 'Node A',
      'type' => 'test',
    ]);

    $a->image->generateSampleItems(1);

    $a->save();

    $result = $this->executeQueryFile('image.gql', ['path' => '/node/' . $a->id()]);
    $image = $result['data']['route']['node']['image'];

    $this->assertEquals($a->image->alt, $image['alt'], 'Alt text correct.');
    $this->assertEquals($a->image->title, $image['title'], 'Title text correct.');
    $this->assertEquals($a->image->entity->url(), $image['url'], 'Retrieve correct image url.');
  }

}
