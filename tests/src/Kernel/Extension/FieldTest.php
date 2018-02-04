<?php

namespace Drupal\Tests\graphql\Kernel\Extension;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test plugin based schema generation.
 *
 * @group graphql
 */
class FieldTest extends GraphQLTestBase {
  public static $modules = [
    'graphql_plugin_test',
  ];

  /**
   * Test if the schema is created properly.
   */
  public function testRootField() {
    $string = 'Hello Echo!';
    $query = $this->getQueryFromFile('echo.gql');
    $this->assertResults($query, ['input' => $string], [
      'echo' => $string,
    ], $this->defaultCacheMetaData());
  }

}
