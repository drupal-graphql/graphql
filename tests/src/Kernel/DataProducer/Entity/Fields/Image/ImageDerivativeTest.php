<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer\Entity\Fields\Image;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\file\FileInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test class for the ImageDerivative data producer.
 */
class ImageDerivativeTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['image', 'file'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->dataProducerManager = $this->container->get('plugin.manager.graphql.data_producer');

    $this->file_uri = 'public://test.jpg';

    $this->file = $this->getMockBuilder(FileInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->file->method('getFileUri')->willReturn($this->file_uri);
    $this->file->method('access')->willReturn((new AccessResultAllowed())->addCacheTags(['test_tag']));
    $this->file->width = 600;
    $this->file->height= 400;

    $this->style = ImageStyle::create(['name' => 'test_style']);
    $effect = [
      'id' => 'image_resize',
      'data' => [
        'width' => 300,
        'height' => 200,
      ],
    ];
    $this->style->addImageEffect($effect);
    $this->style->save();

    $this->file_not_accessible = $this->getMockBuilder(FileInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->file_not_accessible->method('access')->willReturn((new AccessResultForbidden())->addCacheTags(['test_tag_forbidden']));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\Fields\Image\ImageDerivative::resolve
   */
  public function testImageDerivative() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'image_derivative',
      'configuration' => []
    ]);

    // Test that we get the proper style and dimensions if we have access to the
    // file.
    $metadata = $this->defaultCacheMetaData();
    $output = $plugin->resolve($this->file, 'test_style', $metadata);
    $this->assertEquals(
      [
        'url' => $this->style->buildUrl($this->file_uri),
        'width' => 300,
        'height' => 200,
      ],
      $output
    );
    $this->assertContains('config:image.style.test_style', $metadata->getCacheTags());
    $this->assertContains('test_tag', $metadata->getCacheTags());

    // Test that we don't get the derivative if we don't have access to the
    // original file, but we still get the access result cache tags.
    $metadata = $this->defaultCacheMetaData();
    $output = $plugin->resolve($this->file_not_accessible, 'test_style', $metadata);
    $this->assertNull($output);
    $this->assertContains('test_tag_forbidden', $metadata->getCacheTags());
  }

}
