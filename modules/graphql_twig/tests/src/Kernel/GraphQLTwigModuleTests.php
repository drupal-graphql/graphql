<?php

namespace Drupal\Tests\graphql_twig\Kernel;

use Drupal\graphql_plugin_test\GarageInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that test GraphQL theme integration on module level.
 */
class GraphQLTwigModuleTests extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql',
    'graphql_core',
    'graphql_plugin_test',
    'graphql_twig',
    'graphql_twig_test',
  ];

  /**
   * Test if a simple query without fragments works.
   */
  public function testSimpleQuery() {
    $element = [
      '#theme' => 'graphql_echo',
      '#input' => 'This is a test.',
    ];
    $result = $this->container->get('renderer')->renderRoot($element);
    $this->assertEquals('<strong>This is a test.</strong>', $result);
  }

  /**
   * Test query assembly.
   */
  public function testQueryAssembly() {
    $vehicles = [
      ['type' => 'Car', 'wheels' => 4, 'engine' => 'fuel'],
      ['type' => 'Car', 'wheels' => 4, 'engine' => 'diesel'],
      ['type' => 'Bike', 'wheels' => 2, 'gears' => 21],
    ];

    $prophecy = $this->prophesize(GarageInterface::class);
    $prophecy->getVehicles()->willReturn($vehicles);
    $this->container->set('graphql_test.garage', $prophecy->reveal());

    $element = ['#theme' => 'graphql_garage'];
    $result = $this->container->get('renderer')->renderRoot($element);

    $this->assertEquals(implode("\n", [
      'Garage:',
      'A Car with 4 wheels.',
      'A Car with 4 wheels.',
      'A Bike with 2 wheels.',
    ]), trim($result));
  }

}
