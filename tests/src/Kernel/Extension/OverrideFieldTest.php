<?php

namespace Drupal\Tests\graphql\Kernel\Extension;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test plugin based schema generation.
 *
 * @group graphql
 */
class OverrideFieldTest extends GraphQLTestBase {
  public static $modules = [
    'graphql_plugin_test',
    'graphql_override_test',
  ];

  /**
   * Test if the schema is created properly.
   */
  public function testEcho() {
    $string = 'Hello Echo!';
    $query = $this->getQueryFromFile('echo.gql');
    $this->assertResults($query, ['input' => $string], [
      'echo' => strtoupper($string),
    ], $this->defaultCacheMetaData());
  }

}
