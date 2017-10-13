<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql_plugin_test\GarageInterface;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;

/**
 * Test plugin based schema generation.
 *
 * @group graphql
 */
class TypeTest extends GraphQLFileTestBase {
  public static $modules = [
    'graphql_plugin_test',
  ];

  /**
   * Test if the schema is created properly.
   */
  public function testQuery() {
    $vehicles = [
      ['type' => 'Car', 'wheels' => 4, 'engine' => 'fuel'],
      ['type' => 'Car', 'wheels' => 4, 'engine' => 'diesel'],
      ['type' => 'Bike', 'wheels' => 2, 'gears' => 21],
    ];

    $prophecy = $this->prophesize(GarageInterface::class);
    $prophecy->getVehicles()->willReturn($vehicles);
    $this->container->set('graphql_test.garage', $prophecy->reveal());

    $values = $this->executeQueryFile('garage.gql');
    $this->assertArrayNotHasKey('errors', $values);
    $this->assertArraySubset($vehicles, $values['data']['garage']);
  }

}
