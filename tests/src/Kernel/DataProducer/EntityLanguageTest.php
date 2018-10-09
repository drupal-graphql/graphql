<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\DataProducerTestBase;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Data producers test base class.
 *
 * @coversDefaultClass \Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLanguage
 *
 * @group graphql
 */
class EntityLanguageTest extends DataProducerTestBase {

  /**
   * @covers ::resolve
   */
  public function testResolve() {
    $entity = $this->createMockEntity();
    $language = $this->getMockBuilder(LanguageInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $entity->expects($this->once())
      ->method('language')
      ->willReturn($language);

    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'entity_language',
      'configuration' => []
    ]);
    $this->assertEquals($language, $plugin->resolve($entity));
  }

}
