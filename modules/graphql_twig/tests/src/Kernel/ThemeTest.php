<?php

namespace Drupal\Tests\graphql_twig\Kernel;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Session\AccountProxy;
use Drupal\graphql\QueryProcessor;
use Drupal\graphql\QueryResult;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql_core\Traits\GraphQLFileTestTrait;
use Prophecy\Argument;

/**
 * Tests that test GraphQL theme integration on module level.
 */
class ThemeTest extends KernelTestBase {
  use GraphQLFileTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'graphql',
    'graphql_twig',
  ];

  /**
   * A query processor prophecy.
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $processor;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Mock a user that is allowed to do everything.
    $currentUser = $this->prophesize(AccountProxy::class);
    $currentUser->isAuthenticated()->willReturn(TRUE);
    $currentUser->hasPermission(Argument::any())->willReturn(TRUE);
    $currentUser->id()->willReturn(0);
    $currentUser->getRoles()->willReturn(['anonymous']);
    $this->container->set('current_user', $currentUser->reveal());

    // Prepare a mock graphql processor.
    $this->processor = $this->prophesize(QueryProcessor::class);
    $this->container->set('graphql.query_processor', $this->processor->reveal());

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
    /** @var \Prophecy\Prophecy\MethodProphecy $process */
    $process = $this->processor
      ->processQuery($this->getQuery('garage.gql'), [])
      ->willReturn(new QueryResult([], new CacheableMetadata()))
      ->shouldBeCalled();

    $element = ['#theme' => 'graphql_garage'];
    $this->render($element);
  }

  /**
   * Test if a template is turned into a theme hook automatically.
   */
  public function testAutoThemeHook() {
    $testString = 'This is a test.';
    $this->processor
      ->processQuery($this->getQuery('echo.gql'), [
        'input' => $testString,
      ])
      ->willReturn(new QueryResult([
        'data' => [
          'echo' => $testString,
        ]
      ], new CacheableMetadata()))
      ->shouldBeCalled();

    $element = [
      '#theme' => 'graphql_echo',
      '#input' => $testString,
    ];

    $result = $this->render($element);

    $this->assertContains('<strong>' . $testString . '</strong>', $result);
  }

  /**
   * Test if a template suggestion is rendered with it's own query.
   */
  public function testTemplateSuggestion() {
    $testString = 'This is a test.';
    $this->processor
      ->processQuery($this->getQuery('echo_suggestion.gql'), [
        'input' => $testString,
      ])
      ->willReturn(new QueryResult([
        'data' => [
          'suggestion' => $testString,
        ]
      ], new CacheableMetadata()))
      ->shouldBeCalled();

    $element = [
      '#theme' => 'graphql_echo__suggestion',
      '#input' => 'This is a test.',
    ];

    $result = $this->render($element);
    $this->assertContains('<em>' . $testString . '</em>', $result);
  }

}
