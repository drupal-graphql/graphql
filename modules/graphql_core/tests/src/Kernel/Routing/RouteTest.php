<?php

namespace Drupal\Tests\graphql_core\Kernel\Routing;

use Drupal\Core\Path\AliasManagerInterface;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;

/**
 * Test plugin based schema generation.
 *
 * @group graphql_core
 */
class RouteTest extends GraphQLFileTestBase {
  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  public static $modules = [
    'graphql_core',
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
    $values = $this->executeQueryFile('routing.gql');
    $this->assertEquals([
      'internal' => '/graphql/test/a',
      'aliased' => '/my/other/alias',
      'routed' => TRUE,
    ], $values['data']['route'], 'Routes and aliases are resolved properly.');
  }

  /**
   * Test if the schema is created properly.
   */
  public function testDeniedRoute() {
    $values = $this->executeQueryFile('routing.gql');
    $this->assertNull($values['data']['denied'], 'Denied route returns null.');
  }

}
