<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer\Entity\Fields\Image;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\file\FileInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test class for the ImageUrl data producer.
 *
 * @group graphql
 */
class ImageUrlTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->dataProducerManager = $this->container->get('plugin.manager.graphql.data_producer');

    $this->file_uri = 'public://test.jpg';
    $this->file_url = file_create_url($this->file_uri);

    $this->file = $this->getMockBuilder(FileInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->file->method('getFileUri')->willReturn($this->file_uri);
    $this->file->method('access')->willReturn((new AccessResultAllowed())->addCacheTags(['test_tag']));

    $this->file_not_accessible = $this->getMockBuilder(FileInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->file_not_accessible->method('access')->willReturn((new AccessResultForbidden())->addCacheTags(['test_tag_forbidden']));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\Fields\Image\ImageUrl::resolve
   */
  public function testImageUrl() {
    // Test that we get a file we have access to.
    $result = $this->executeDataProducer('image_url', [
      'entity' => $this->file,
    ]);

    $this->assertEquals($this->file_url, $result);

    // TODO: Add cache checks.
//    $this->assertContains('test_tag', $metadata->getCacheTags());

    // Test that we do not get a file we don't have access to, but the cache
    // tags are still added.
    $result = $this->executeDataProducer('image_url', [
      'entity' => $this->file_not_accessible,
    ]);

    $this->assertNull($result);

    // TODO: Add cache checks.
//    $this->assertContains('test_tag_forbidden', $metadata->getCacheTags());
  }

}
