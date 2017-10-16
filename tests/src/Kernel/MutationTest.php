<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql_plugin_test\GarageInterface;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;

/**
 * Test a simple mutation.
 *
 * @group graphql
 */
class MutationTest extends GraphQLFileTestBase {
  public static $modules = [
    'graphql_plugin_test',
  ];

  /**
   * Test if the schema is created properly.
   */
  public function testMutationQuery() {
    $car = ['engine' => 'electric'];

    $prophecy = $this->prophesize(GarageInterface::class);
    $prophecy
      ->insertVehicle($car)
      ->willReturn([
        'type' => 'Car',
        'wheels' => 4,
        'engine' => 'electric',
      ])->shouldBeCalled();

    $this->container->set('graphql_test.garage', $prophecy->reveal());

    $this->executeQueryFile('buy_car.gql', [
      'car' => $car,
    ]);
  }

}
