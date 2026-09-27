<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer\Images;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Test class for the ImageResourceUrl data producer.
 *
 * @group graphql
 */
#[Group('graphql')]
#[RunTestsInSeparateProcesses]
#[AllowMockObjectsWithoutExpectations]
class ImageResourceUrlTest extends GraphQLTestBase {

  /**
   * Tests the ImageResourceUrl data producer.
   *
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Images\ImageResourceUrl::resolve
   *
   * @dataProvider imageResourceUrlProvider
   */
  #[DataProvider('imageResourceUrlProvider')]
  public function testImageResourceUrl(array $input, string $expected): void {
    $result = $this->executeDataProducer('image_style_url', [
      'derivative' => $input,
    ]);

    $this->assertEquals($expected, $result);
  }

  /**
   * Provider for testImageResourceUrl().
   */
  public static function imageResourceUrlProvider(): array {
    return [
      [
        ['url' => 'http://localhost/test_image.jpg'],
        'http://localhost/test_image.jpg',
      ],
      [
        [],
        '',
      ],
    ];
  }

}
