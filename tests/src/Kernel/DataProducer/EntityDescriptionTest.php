<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\DataProducerTestBase;
use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Data producers test base class.
 *
 * @coversDefaultClass \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityDescription
 *
 * @group graphql
 */
class EntityDescriptionTest extends DataProducerTestBase {

  /**
   * @covers ::resolve
   *
   * @dataProvider resolveProvider
   */
  public function testResolve($input) {
    $entity = $this->getMockBuilder([EntityInterface::class, EntityDescriptionInterface::class])
      ->disableOriginalConstructor()
      ->getMock();

    $entity->expects($this->once())
      ->method('getDescription')
      ->willReturn($input);

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_description',
      'configuration' => []
    ]);
    $this->assertEquals($input, $plugin->resolve($entity));
  }

  public function resolveProvider() {
    return [
      [
        '1970-01-01T10:00:00+1000',
      ],
      [
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla eget pretium ex. Donec diam sem, varius et nisl a, facilisis scelerisque purus. Sed ligula nisi, aliquet vel lorem id, feugiat hendrerit nunc.',
      ],
    ];
  }

}
