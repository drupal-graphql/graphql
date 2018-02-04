<?php

namespace Drupal\Tests\graphql\Kernel\Extension;

use Drupal\graphql_plugin_test\GarageInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test plugin based schema generation.
 *
 * @group graphql
 */
class OverrideTypeTest extends GraphQLTestBase {
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

    $query = $this->getQueryFromFile('fancy_garage.gql');
    $this->assertResults($query, [], [
      'garage' => [
        0 => [
          'wheels' => 4,
          'type' => 'Car',
          'engine' => 'fuel',
        ],
        1 => [
          'wheels' => 3,
          'gadgets' => ['Phone charger', 'GPS', 'Coffee machine'],
          'type' => 'Bike',
          'gears' => 21,
        ],
      ],
    ], $this->defaultCacheMetaData());
  }

}
