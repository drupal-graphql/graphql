<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer\Entity\Fields\Image;

use Drupal\file\FileInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

class ImageUrlTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->dataProducerManager = $this->container->get('plugin.manager.graphql.data_producer');
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\Fields\Image\ImageUrl::resolve
   */
  public function testImageUrl() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'image_url',
      'configuration' => []
    ]);

    $file_uri = 'public://test.jpg';
    $file_url = file_create_url($file_uri);

    $file = $this->getMockBuilder(FileInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $file->method('getFileUri')->willReturn($file_uri);
    $file->method('access')->willReturn(TRUE);
    $output = $plugin->resolve($file);
    $this->assertEquals($file_url, $output);

    $file2 = $this->getMockBuilder(FileInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $file2->method('getFileUri')->willReturn($file_uri);
    $file2->method('access')->willReturn(FALSE);
    $output = $plugin->resolve($file2);
    $this->assertNull($output);
  }

}
