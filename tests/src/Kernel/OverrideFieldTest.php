<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;

/**
 * Test plugin based schema generation.
 *
 * @group graphql
 */
class OverrideFieldTest extends GraphQLFileTestBase {
  public static $modules = [
    'graphql_plugin_test',
    'graphql_override_test',
  ];

  /**
   * Test if the schema is created properly.
   */
  public function testEcho() {
    $string = 'Hello Echo!';
    $values = $this->executeQueryFile('echo.gql', ['input' => $string]);
    $this->assertEquals($values['data']['echo'], strtoupper($string));
  }

}
