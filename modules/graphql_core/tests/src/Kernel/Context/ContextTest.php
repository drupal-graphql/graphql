<?php

namespace Drupal\Tests\graphql_core\Kernel\Context;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test plugin based schema generation.
 *
 * @group graphql_core
 */
class ContextTest extends GraphQLTestBase {

  public static $modules = [
    'graphql_core',
    'graphql_context_test',
  ];

  /**
   * Test if the schema is created properly.
   */
  public function testSimpleContext() {
    $query = $this->getQueryFromFile('context.gql');
    $this->assertResults($query, [], [
      'a' => ['name' => 'graphql_context_test.a'],
      'b' => ['name' => 'graphql_context_test.b'],
    ], $this->defaultCacheMetaData());
  }

}
