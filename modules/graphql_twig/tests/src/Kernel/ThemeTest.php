<?php

namespace Drupal\Tests\graphql_twig\Kernel;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Cache\Context\ContextCacheKeys;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\graphql\QueryResult;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql_core\Traits\GraphQLFileTestTrait;
use Drupal\Tests\graphql_twig\Traits\ThemeTestTrait;
use Prophecy\Argument;

/**
 * Tests that test GraphQL theme integration on module level.
 */
class ThemeTest extends KernelTestBase {
  use GraphQLFileTestTrait;
  use ThemeTestTrait;

  /**
   * @var CacheContextsManager
   */
  protected $contextManager;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'graphql',
    'graphql_twig',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->setupThemeTest();
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
   * Test query assembly.
   */
  public function testRenderCache() {

    $metadata = new CacheableMetadata();
    $metadata->setCacheMaxAge(0);

    $process = $this->processor
      ->processQuery($this->getQuery('garage.gql'), [])
      ->willReturn(new QueryResult([], $metadata));

    $element = [
      '#theme' => 'graphql_garage',
    ];

    $renderer = $this->container->get('renderer');

    $renderer->renderRoot($element);
    $renderer->renderRoot($element);

    $process->shouldHaveBeenCalledTimes(2);
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

}
