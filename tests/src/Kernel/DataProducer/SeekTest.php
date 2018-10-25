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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->dataProducerManager = $this->container->get('plugin.manager.graphql.data_producer');
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Utility\Seek::resolve
   *
   * @dataProvider testSeekProvider
   */
  public function testSeek($input, $position, $expected) {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'seek',
      'configuration' => []
    ]);
    $this->assertEquals($expected, $plugin->resolve($input, $position));
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
