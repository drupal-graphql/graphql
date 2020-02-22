<?php

namespace Drupal\Tests\graphql_core\Kernel\Routing;

use Drupal\Core\GeneratedUrl;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Tests\graphql_core\Kernel\GraphQLCoreTestBase;
use Prophecy\Argument;

/**
 * Test plugin based schema generation.
 *
 * @group graphql_core
 */
class RouteTest extends GraphQLCoreTestBase {

  public static $modules = [
    'graphql_context_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $aliasManager = $this->prophesize(AliasManagerInterface::class);
    $aliasManager
      ->getAliasByPath('/graphql/test/a', Argument::any())
      ->willReturn('/my/other/alias');

    $urlGenerator = $this->prophesize(UrlGeneratorInterface::class);
    $urlGenerator
      ->getPathFromRoute('graphql_context_test.a', [])
      ->willReturn('graphql/test/a');

    $urlGenerator
      ->generateFromRoute('graphql_context_test.a', [], ['query' => []], TRUE)
      ->willReturn((new GeneratedUrl())->setGeneratedUrl('/my/other/alias'));

    $this->container->set('path_alias.manager', $aliasManager->reveal());
    $this->container->set('url_generator', $urlGenerator->reveal());
  }

  /**
   * Test if the schema is created properly.
   */
  public function testRoute() {
    $metadata = $this->defaultCacheMetaData();
    $metadata->addCacheTags([
      '4xx-response',
    ]);

    $this->assertResults($this->getQueryFromFile('routing.gql'), [], [
      'route' => [
        'path' => '/my/other/alias',
        'internal' => '/graphql/test/a',
        'alias' => '/my/other/alias',
      ],
      'denied' => NULL,
    ], $metadata);
  }

}
