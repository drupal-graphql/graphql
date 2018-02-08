<?php

namespace Drupal\Tests\graphql_core\Kernel\Routing;

use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Tests\graphql_core\Kernel\GraphQLCoreTestBase;

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
    $aliasManager->getPathByAlias('/my/alias')->willReturn('/graphql/test/a');
    $aliasManager->getAliasByPath('/graphql/test/a')->willReturn('/my/other/alias');
    $aliasManager->getAliasByPath('/graphql/test/c')->willReturn('/graphql/test/c');
    $this->container->set('path.alias_manager', $aliasManager->reveal());
  }

  /**
   * Test if the schema is created properly.
   */
  public function testRoute() {
    // TODO: Check cache metadata.
    $metadata = $this->defaultCacheMetaData();
    $metadata->addCacheTags([
      '4xx-response',
    ]);

    $this->assertResults($this->getQueryFromFile('routing.gql'), [], [
      'route' => [
        'internal' => '/graphql/test/a',
        'aliased' => '/my/other/alias',
        'routed' => TRUE,
      ],
      'denied' => NULL,
    ], $metadata);
  }

}
