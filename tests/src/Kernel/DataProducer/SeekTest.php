<?php

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
   * @dataProvider testSeekProvider
   */
  public function testSeek($input, $position, $expected) {
    $result = $this->executeDataProducer('seek', [
      'input' => $input,
      'position' => $position,
    ]);

    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testSeek().
   *
   * @return array
   */
  public function testSeekProvider() {
    return [
      [
        [1,2,3],
        0,
        1,
      ],
      [
        [1,2,3],
        1,
        2,
      ],
      [
        [1,2,3],
        3,
        NULL,
      ],
      [
        [1,[2],3],
        1,
        [2],
      ],
      // For now, we do not support negative indices.
      [
        [1,2,3],
        -1,
        NULL,
      ]
    ];
  }
}
