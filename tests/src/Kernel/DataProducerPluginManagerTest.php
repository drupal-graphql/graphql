<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\graphql\GraphQL\ResolverBuilder;

/**
 * @coversDefaultClass \Drupal\graphql\Plugin\DataProducerPluginManager
 *
 * @requires module typed_data
 *
 * @group graphql
 */
class DataProducerPluginManagerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql',
    'typed_data'
  ];

  /**
   * Data producer manager.
   *
   * @var \Drupal\graphql\Plugin\DataProducerPluginManager
   */
  protected $dataProducerManager;

  /**
   * @var \Drupal\graphql\GraphQL\ResolverBuilder
   */
  protected $builder;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->dataProducerManager = $this->container->get('plugin.manager.graphql.data_producer');
    $this->builder = new ResolverBuilder();
  }

  /**
   * @covers ::getInstance
   */
  public function testGetInstance() {
    $instance = $this->dataProducerManager->getInstance([
      'id' => 'entity_load',
      'configuration' => [
        'mapping' => [
          'entity_type' => $this->builder->fromValue('node'),
          'entity_id' => $this->builder->fromArgument('id'),
        ],
      ],
    ]);

    $this->assertEquals('entity_load', $instance->getPluginId());
    $instance = $this->dataProducerManager->getInstance([
      'id' => 'uppercase',
      'configuration' => [
        'mapping' => [
          'string' => $this->builder->fromParent(),
        ],
      ],
    ]);

    $this->assertEquals('uppercase', $instance->getPluginId());
  }

}
