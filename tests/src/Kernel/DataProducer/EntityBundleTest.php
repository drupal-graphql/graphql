<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\DataProducerTestBase;
use Drupal\Tests\UnitTestCase;

/**
 * Data producers test base class.
 *
 * @coversDefaultClass \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityBundle
 *
 * @group graphql
 */
class EntityBundleTest extends DataProducerTestBase {

  /**
   * @covers ::resolve
   *
   * @dataProvider resolveProvider
   */
  public function testResolve($input) {
    $entity = $this->createMockEntity();
    $entity->expects($this->once())
      ->method('bundle')
      ->willReturn($input);

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_bundle',
      'configuration' => []
    ]);
    $this->assertEquals($input, $plugin->resolve($entity));
  }

  public function resolveProvider() {
    return [
      ['page'],
      ['article']
    ];
  }

}
