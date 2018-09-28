<?php

namespace Drupal\Tests\graphql_core\Kernel\Context;

use Drupal\Tests\graphql_core\Kernel\GraphQLCoreTestBase;

/**
 * Test plugin based schema generation.
 *
 * @group graphql_core
 */
class ContextTest extends GraphQLCoreTestBase {

  public static $modules = [
    'graphql_context_test',
  ];

  /**
   * Test if the schema is created properly.
   */
  public function testSimpleContext() {
    $query = $this->getQueryFromFile('context.gql');
    $metadata = $this->defaultCacheMetaData();

    $this->assertResults($query, [], [
      'a' => ['name' => 'graphql_context_test.a'],
      'b' => ['name' => 'graphql_context_test.b'],
    ], $metadata);
  }

}
