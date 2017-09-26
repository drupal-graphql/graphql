<?php

namespace Drupal\Tests\graphql_twig\Kernel;

use Drupal\graphql_plugin_test\GarageInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that test GraphQL theme integration on module level.
 */
class GraphQLTwigThemeTests extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'graphql',
    'graphql_core',
    'graphql_plugin_test',
    'graphql_twig',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');

    $themeName = 'graphql_twig_test_theme';

    /** @var \Drupal\Core\Extension\ThemeHandler $themeHandler */
    $themeHandler = $this->container->get('theme_handler');
    /** @var \Drupal\Core\Theme\ThemeInitialization $themeInitialization */
    $themeInitialization = $this->container->get('theme.initialization');
    /** @var \Drupal\Core\Theme\ThemeManager $themeManager */
    $themeManager = $this->container->get('theme.manager');

    $themeHandler->install([$themeName]);
    $theme = $themeInitialization->initTheme($themeName);
    $themeManager->setActiveTheme($theme);
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
      'A Bike with 21 gears.',
    ]), trim($result));
  }

  /**
   * Test if the theme is able to override a graphql template.
   */
  public function testTemplateOverride() {
    $element = [
      '#theme' => 'graphql_echo',
      '#input' => 'This is a test.',
    ];

    /** @var \Drupal\Core\Render\Renderer $renderer */
    $renderer = $this->container->get('renderer');

    $result = $result = $renderer->renderRoot($element);
    $this->assertEquals('<strong>This is a test.</strong>', $result);
  }

  /**
   * Test if a template suggestion is rendered with it's own query.
   */
  public function testTemplateSuggestion() {
    $element = [
      '#theme' => 'graphql_echo__suggestion',
      '#input' => 'This is a test.',
    ];

    /** @var \Drupal\Core\Render\Renderer $renderer */
    $renderer = $this->container->get('renderer');

    $result = $result = $renderer->renderRoot($element);
    $this->assertEquals('<em>This is a test.</em>', $result);
  }

  /**
   * Test query assembly.
   */
  public function testQuerySuggestions() {
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
      'A Bike with 21 gears.',
    ]), trim($result));
  }

}
