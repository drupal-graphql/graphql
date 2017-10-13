<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql_plugin_test\GarageInterface;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;

/**
 * Test plugin based schema generation.
 *
 * @group graphql
 */
class OverrideTypeTest extends GraphQLFileTestBase {
  public static $modules = [
    'graphql_plugin_test',
    'graphql_override_test',
  ];

  /**
   * Test if the schema is created properly.
   */
  public function testOverriddenTypes() {
    $vehicles = [
      ['type' => 'Car', 'wheels' => 4, 'engine' => 'fuel'],
      ['type' => 'Bike', 'wheels' => 2, 'gears' => 21],
    ];

    $prophecy = $this->prophesize(GarageInterface::class);
    $prophecy->getVehicles()->willReturn($vehicles);
    $this->container->set('graphql_test.garage', $prophecy->reveal());

    $values = $this->executeQueryFile('fancy_garage.gql');
    $garage = $values['data']['garage'];

    $this->assertEquals(4, $garage[0]['wheels'], 'The car still has 4 wheels.');
    $this->assertEquals(3, $garage[1]['wheels'], 'The bike has three wheels now.');
    $this->assertEquals([
      'Phone charger', 'GPS', 'Coffee machine',
    ], $garage[1]['gadgets'], 'The bike is owned by a Drupal developer.');
  }

}
