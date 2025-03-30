<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Data producers Seek test class.
 *
 * @group graphql
 */
class SeekTest extends GraphQLTestBase {

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Utility\Seek::resolve
   *
   * @dataProvider seekProvider
   *
   * @param array $input
   *   The test list to seek in.
   * @param int $position
   *   The position in the list to retrieve.
   * @param int|array|null $expected
   *   The expected result returned by the data producer.
   */
  public function testSeek(array $input, int $position, int|array|null $expected): void {
    $result = $this->executeDataProducer('seek', [
      'input' => $input,
      'position' => $position,
    ]);

    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testSeek().
   */
  public static function seekProvider(): array {
    return [
      [
        [1, 2, 3],
        0,
        1,
      ],
      [
        [1, 2, 3],
        1,
        2,
      ],
      [
        [1, 2, 3],
        3,
        NULL,
      ],
      [
        [1, [2], 3],
        1,
        [2],
      ],
      // For now, we do not support negative indices.
      [
        [1, 2, 3],
        -1,
        NULL,
      ],
    ];
  }

}
