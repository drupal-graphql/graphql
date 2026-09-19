<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Data producers Seek test class.
 *
 * @group graphql
 */
#[Group('graphql')]
#[RunTestsInSeparateProcesses]
class SeekTest extends GraphQLTestBase {

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Utility\Seek::resolve
   *
   * @dataProvider seekProvider
   *
   * @param array|null $input
   *   The test list to seek in.
   * @param int $position
   *   The position in the list to retrieve.
   * @param int|array|null $expected
   *   The expected result returned by the data producer.
   */
  #[DataProvider('seekProvider')]
  public function testSeek(?array $input, int $position, int|array|null $expected): void {
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
      // NULL input should resolve to NULL, not throw.
      [
        NULL,
        0,
        NULL,
      ],
    ];
  }

}
