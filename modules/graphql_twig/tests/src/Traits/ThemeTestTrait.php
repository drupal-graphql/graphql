<?php

namespace Drupal\Tests\graphql_twig\Traits;

use Drupal\Core\Session\AccountProxy;
use Drupal\graphql\GraphQL\Execution\QueryProcessor;
use Drupal\KernelTests\KernelTestBase;
use Prophecy\Argument;

trait ThemeTestTrait {

  /**
   * A query processor prophecy.
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $processor;

  /**
   * Initialize the test theme.
   */
  protected function setupThemeTest() {
    if ($this instanceof KernelTestBase) {
      // Mock a user that is allowed to do everything.
      $currentUser = $this->prophesize(AccountProxy::class);
      $currentUser->isAuthenticated()->willReturn(TRUE);
      $currentUser->hasPermission(Argument::any())->willReturn(TRUE);
      $currentUser->id()->willReturn(1);
      $currentUser->getRoles()->willReturn(['administrator']);
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
  }
}