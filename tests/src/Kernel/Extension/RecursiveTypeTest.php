<?php

namespace Drupal\Tests\graphql\Kernel\Extension;

use Drupal\graphql_plugin_test\GarageInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test plugin based schema generation.
 *
 * @group graphql
 */
class RecursiveTypeTest extends GraphQLTestBase {
  public static $modules = [
    'graphql_plugin_test',
  ];

  /**
   * Test if the schema is created properly.
   */
  public function testRecursiveType() {
    $vehicles = [
      [
        'type' => 'Car',
        'backupCar' => [
          'type' => 'Car',
          'engine' => 'electric',
        ],
      ],
    ];

    $prophecy = $this->prophesize(GarageInterface::class);
    $prophecy->getVehicles()->willReturn($vehicles);
    $this->container->set('graphql_test.garage', $prophecy->reveal());

    $query = $this->getQueryFromFile('recursive_garage.gql');
    $this->assertResults($query, [], [
      'garage' => [
        0 => [
          'backupCar' => [
            'engine' => 'electric',
          ],
        ],
      ],
    ], $this->defaultCacheMetaData());
  }

}
