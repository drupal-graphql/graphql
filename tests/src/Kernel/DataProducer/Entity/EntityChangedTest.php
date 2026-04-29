<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer\Entity;

use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test class for the entity_changed data producer.
 *
 * @group graphql
 */
class EntityChangedTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->config('system.date')
      ->set('timezone.default', 'UTC')
      ->save();
  }

  /**
   * Test it works if getChangedTime returns a string.
   *
   * This is the case for many Drupal entities where the types that are loaded
   * as strings from the database are not properly coerced.
   */
  public function testWorksWithStringTimestamp(): void {
    $entity = $this->createMock(EditorialContentEntityBase::class);
    $entity->method("getChangedTime")
      ->willReturn("1743465600");

    $this->assertEquals(
      "2025-04-01T00:00:00+0000",
      $this->executeDataProducer('entity_changed', [
        'entity' => $entity,
      ])
    );
  }

  /**
   * Test it works if getChangedTime returns an int.
   */
  public function testWorksWithIntTimestamp(): void {
    $entity = $this->createMock(EditorialContentEntityBase::class);
    $entity->method("getChangedTime")
      ->willReturn(1743465600);

    $this->assertEquals(
      "2025-04-01T00:00:00+0000",
      $this->executeDataProducer('entity_changed', [
        'entity' => $entity,
      ])
    );
  }

  /**
   * Test it allows specifying a format.
   */
  public function testAllowsSpecifyingFormat(): void {
    $entity = $this->createMock(EditorialContentEntityBase::class);
    $entity->method("getChangedTime")
      ->willReturn(1743465600);

    $this->assertEquals(
      "1743465600",
      $this->executeDataProducer('entity_changed', [
        'entity' => $entity,
        'format' => 'U',
      ])
    );
  }

  /**
   * Test it returns NULL for non-changed entities.
   */
  public function testNullForNonChangedEntities(): void {
    $entity = $this->createMock(EntityInterface::class);

    $this->assertEquals(
      NULL,
      $this->executeDataProducer('entity_changed', [
        'entity' => $entity,
      ])
    );
  }

}
