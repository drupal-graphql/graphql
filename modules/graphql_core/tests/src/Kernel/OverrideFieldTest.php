<?php

namespace Drupal\Tests\graphql_core\Kernel;

/**
 * Test plugin based schema generation.
 *
 * @group graphql_core
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
