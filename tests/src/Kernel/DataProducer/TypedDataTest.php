<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Data producers TypedData test class.
 *
 * @requires module typed_data
 *
 * @group graphql
 */
class TypedDataTest extends GraphQLTestBase {

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\TypedData\PropertyPath::resolve
   */
  public function testPropertyPath() {
    $manager = $this->createMock(TypedDataManagerInterface::class);

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

    $manager->expects($this->any())
      ->method('createDataDefinition')
      ->willReturn(DataDefinition::create('tree'));

    $manager->expects($this->any())
      ->method('create')
      ->willReturn($tree_type->reveal());

    $this->container->set('typed_data_manager', $manager);

    $value = [
      'path' => [
        'uri' => '<front>',
        'path_name' => 'Front page',
      ],
    ];

    $this->assertEquals('<front>', $this->executeDataProducer('property_path', [
      'path' => 'path.uri',
      'type' => 'tree',
      'value' => $value,
    ]));

    $this->assertEquals('Front page', $this->executeDataProducer('property_path', [
      'path' => 'path.path_name',
      'type' => 'tree',
      'value' => $value,
    ]));
  }

}
