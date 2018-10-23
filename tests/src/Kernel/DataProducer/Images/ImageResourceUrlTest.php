<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer\Images;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test class for the ImageResourceUrl data producer.
 *
 * @group graphql
 */
class ImageResourceUrlTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->dataProducerManager = $this->container->get('plugin.manager.graphql.data_producer');
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Images\ImageResourceUrl::resolve
   *
   * @dataProvider testImageResourceUrlProvider
   */
  public function testImageResourceUrl($input, $expected) {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'image_style_url',
      'configuration' => []
    ]);
    $this->assertEquals($expected, $plugin->resolve($input));
  }

  /**
   * Provider for testImageResourceUrl().
   */
  public function testImageResourceUrlProvider() {
    return [
      [
        ['url' => 'http://localhost/test_image.jpg'],
        'http://localhost/test_image.jpg'
      ],
      [
        [],
        ''
      ]
    ];
  }
}
