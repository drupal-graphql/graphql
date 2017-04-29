<?php

namespace Drupal\Tests\graphql_core;

/**
 * Test plugin based schema generation.
 */
class OverrideFieldTest extends GraphQLFileTest {
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
