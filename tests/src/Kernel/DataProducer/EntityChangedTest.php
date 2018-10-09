<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\DataProducerTestBase;
use Drupal\Tests\UnitTestCase;

/**
 * Data producers test base class.
 *
 * @coversDefaultClass \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityChanged
 *
 * @group graphql
 */
class EntityChangedTest extends DataProducerTestBase {

  /**
   * @covers ::resolve
   *
   * @dataProvider resolveProvider
   */
  public function testResolve($input, $expected) {
    $entity = $this->createMockEntity();
    $entity->expects($this->once())
      ->method('getChangedTime')
      ->willReturn($input['time']);

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_changed',
      'configuration' => []
    ]);
    $this->assertEquals($expected, $plugin->resolve($entity, $input['format']));
  }

  public function resolveProvider() {
    return [
      [
        [
          'time' => 0,
          'format' => NULL
        ],
        '1970-01-01T10:00:00+1000'
      ],
      [
        [
          'time' => 17000000000,
          'format' => 'Y-m-d'
        ],
        '2508-09-16'
      ],
    ];
  }

}
