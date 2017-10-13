<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;

/**
 * Test plugin based schema generation.
 *
 * @group graphql
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
