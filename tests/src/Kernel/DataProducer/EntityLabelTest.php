<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\DataProducerTestBase;
use Drupal\Tests\UnitTestCase;

/**
 * Data producers test base class.
 *
 * @coversDefaultClass \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLabel
 *
 * @group graphql
 */
class EntityLabelTest extends DataProducerTestBase {

  /**
   * @covers ::resolve
   *
   * @dataProvider resolveProvider
   */
  public function testResolve($input) {
    $entity = $this->createMockEntity();
    $entity->expects($this->once())
      ->method('label')
      ->willReturn($input);

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_label',
      'configuration' => []
    ]);
    $this->assertEquals($input, $plugin->resolve($entity));
  }

  public function resolveProvider() {
    return [
      [22532], [2], ['Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla eget pretium ex. Donec diam sem, varius et nisl a, facilisis scelerisque purus. Sed ligula nisi, aliquet vel lorem id, feugiat hendrerit nunc.'], [0], ['test string']
    ];
  }

}
