<?php

namespace Drupal\Tests\graphql_image\Kernel;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
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
    'breakpoint',
    'responsive_image',
    'graphql_core',
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
    ])->setComponent('image', ['type' => 'graphql_image'])->save();

    $this->container->get('config.factory')->getEditable('graphql_content.schema')
      ->set('types', [
        'node' => [
          'exposed' => TRUE,
          'bundles' => [
            'test' => [
              'exposed' => TRUE,
              'view_mode' => 'node.graphql',
            ],
          ],
        ],
      ])
      ->save();

    $responsiveImgStyle = ResponsiveImageStyle::create(array(
      'id' => 'style_one',
      'label' => 'Style One',
      'breakpoint_group' => 'graphql_image',
    ));
    $responsiveImgStyle->addImageStyleMapping('graphql_image.mobile', '1x', array(
      'image_mapping_type' => 'image_style',
      'image_mapping' => 'thumbnail',
    ));
    $responsiveImgStyle->save();
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

    /**
     * Responsive image output should contain the name of the file.
     *
     * @see \Drupal\image\Plugin\Field\FieldType\ImageItem::generateSampleValue()
     */
    $this->assertContains('generateImage', $image['responsive'], 'Proper responsive image returned.');
  }

}
