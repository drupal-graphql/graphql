<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\DataProducerTestBase;
use Drupal\Tests\UnitTestCase;

/**
 * Data producers test base class.
 *
 * @coversDefaultClass \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityId
 *
 * @group graphql
 */
class EntityIdTest extends DataProducerTestBase {

  /**
   * @covers ::resolve
   *
   * @dataProvider resolveProvider
   */
  public function testResolve($input) {
    $entity = $this->createMockEntity();

    $entity->expects($this->once())
      ->method('id')
      ->willReturn($input);

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_id',
      'configuration' => []
    ]);
    $this->assertEquals($input, $plugin->resolve($entity));
  }

  public function resolveProvider() {
    return [
      [22532], [2], [111111122222233333], [0], ['test string']
    ];
  }

}
