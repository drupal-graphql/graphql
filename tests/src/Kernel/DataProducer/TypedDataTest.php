<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Data producers TypedData test class.
 *
 * @requires module typed_data
 *
 * @group graphql
 */
class TypedDataTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->dataProducerManager = $this->container->get('plugin.manager.graphql.data_producer');
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\TypedData\PropertyPath::resolve
   */
  public function testPropertyPath() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'property_path',
      'configuration' => []
    ]);

    $typed_data_manager = $this->getMock(TypedDataManagerInterface::class);

    $typed_data_manager->expects($this->any())
      ->method('getDefinition')
      ->will($this->returnValueMap([
        'tree' => ['class' => '\Drupal\Core\TypedData\ComplexDataInterface'],
      ]));

    $uri = $this->prophesize(TypedDataInterface::class);
    $uri->getValue()
      ->willReturn('<front>');

    $path_name = $this->prophesize(TypedDataInterface::class);
    $path_name->getValue()
      ->willReturn('Front page');

    $path = $this->prophesize(ComplexDataInterface::class);
    $path->get('uri')
      ->willReturn($uri);
    $path->get('path_name')
      ->willReturn($path_name);
    $path->getValue()
      ->willReturn([]);

    $tree_type = $this->prophesize(ComplexDataInterface::class);
    $tree_type->get('path')
      ->willReturn($path);
    $tree_type->getValue()
      ->willReturn([]);

    $typed_data_manager->expects($this->any())
      ->method('create')
      ->willReturn($tree_type->reveal());

    $this->container->set('typed_data_manager', $typed_data_manager);
    $metadata = new CacheableMetadata();

    $this->assertEquals('<front>', $plugin->resolve('path.uri', 'tree', [
      'path' => [
        'uri' => '<front>',
        'path_name' => 'Front page',
      ]
    ], $metadata));

    $this->assertEquals('Front page', $plugin->resolve('path.path_name', 'tree', [
      'path' => [
        'uri' => '<front>',
        'path_name' => 'Front page',
      ]
    ], $metadata));
  }

}
