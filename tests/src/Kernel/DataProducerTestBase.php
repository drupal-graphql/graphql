<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Data producers test base class.
 *
 * @group graphql
 */
abstract class DataProducerTestBase extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'graphql',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->dataProducerManager = $this->container->get('plugin.manager.graphql.data_producer');
  }

  /**
   * Returns a mock entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function createMockEntity() {
    $entity = $this->getMockBuilder([EntityChangedInterface::class, EntityInterface::class])
      ->disableOriginalConstructor()
      ->getMock();
    return $entity;
  }
}
