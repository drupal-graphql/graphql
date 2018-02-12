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
    $aliasManager->getAliasByPath('/graphql/test/a', NULL)->willReturn('/my/other/alias');
    $this->container->set('path.alias_manager', $aliasManager->reveal());
  }

  /**
   * Test if the schema is created properly.
   */
  public function testRoute() {
    // TODO: Check cache metadata.
    $metadata = $this->defaultCacheMetaData();
    $metadata->setCacheTags(array_diff($metadata->getCacheTags(), ['entity_bundles']));
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
