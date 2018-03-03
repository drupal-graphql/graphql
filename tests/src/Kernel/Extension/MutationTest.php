<?php

namespace Drupal\Tests\graphql\Kernel\Extension;

use Drupal\graphql_plugin_test\GarageInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test a simple mutation.
 *
 * @group graphql
 */
class MutationTest extends GraphQLTestBase {
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

    $query = $this->getQueryFromFile('buy_car.gql');
    $this->assertResults($query, ['car' => $car], [
      'buyCar' => [
        '__typename' => 'Car',
      ],
    ], $this->defaultMutationCacheMetaData());
  }

}
