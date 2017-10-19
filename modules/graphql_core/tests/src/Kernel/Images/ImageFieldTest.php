<?php

namespace Drupal\Tests\graphql_core\Kernel\Images;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\image\Entity\ImageStyle;
use Drupal\responsive_image\Entity\ResponsiveImageStyle;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
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
    'graphql_core',
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
    $this->assertEquals($a->image->entity->url(), $image['entity']['url'], 'Retrieve correct image url.');
    $imageStyle = ImageStyle::load('thumbnail');
    $styleUrl = $imageStyle->buildUrl($a->image->entity->uri->value);
    $this->assertEquals($styleUrl, $image['thumbnailImage']['url']);
  }

}
