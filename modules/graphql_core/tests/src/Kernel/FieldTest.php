<?php

namespace Drupal\Tests\graphql_core\Kernel;

/**
 * Test plugin based schema generation.
 */
class FieldTest extends GraphQLFileTest {
  public static $modules = [
    'graphql_plugin_test',
  ];

  /**
   * Test if the schema is created properly.
   */
  public function testRootField() {
    $string = 'Hello Echo!';
    $values = $this->executeQueryFile('echo.gql', ['input' => $string]);
    $this->assertEquals($values['data']['echo'], $string);
  }

}
