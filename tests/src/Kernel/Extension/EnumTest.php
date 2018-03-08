<?php

namespace Drupal\Tests\graphql\Kernel\Extension;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test enumeration support in different ways.
 *
 * @group graphql
 */
class EnumTest extends GraphQLTestBase {

  public static $modules = [
    'graphql_enum_test',
  ];

  /**
   * Test enumeration plugins.
   */
  public function testEnumPlugins() {
    $query = $this->getQueryFromFile('enums.gql');
    $this->assertResults($query, [], [
      'number' => 'ONE',
      'numbers' => [
        'ONE', 'TWO', 'THREE',
      ],
    ], $this->defaultCacheMetaData());
  }

}
