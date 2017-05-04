<?php

namespace Drupal\Tests\graphql_core\Kernel;

/**
 * Test plugin based schema generation.
 *
 * @group graphql_core
 */
class FieldTest extends GraphQLFileTestBase {
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
