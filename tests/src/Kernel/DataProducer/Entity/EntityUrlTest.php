<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test class for the entity_url data producer.
 *
 * @group graphql
 */
class EntityUrlTest extends GraphQLTestBase {

  /**
   * Test that EntityUrl does not fail with a new entity.
   */
  public function testNewEntityReturnsNull(): void {
    $entity = $this->createMock(EntityInterface::class);
    $entity->method('isNew')
      ->willReturn(TRUE);

    $this->assertNull($this->executeDataProducer('entity_url', [
      'entity' => $entity,
    ]));
  }

}
