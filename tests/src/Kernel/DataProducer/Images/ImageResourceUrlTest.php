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
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Images\ImageResourceUrl::resolve
   *
   * @dataProvider testImageResourceUrlProvider
   */
  public function testImageResourceUrl($input, $expected) {
    $result = $this->executeDataProducer('image_style_url', [
      'derivative' => $input,
    ]);

    $this->assertEquals($expected, $result);
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
