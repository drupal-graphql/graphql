<?php

namespace Drupal\Tests\graphql_core\Kernel;

use Drupal\graphql\GraphQL\TypeCollector;
use Youshido\GraphQL\Type\Enum\EnumType;

/**
 * Test enumeration support in different ways.
 *
 * @group graphql_core
 */
class EnumTest extends GraphQLFileTestBase {

  public static $modules = [
    'graphql_enum_test',
  ];

  /**
   * Test enumeration plugins.
   */
  public function testEnumPlugins() {
    $result = $this->executeQueryFile('enums.gql');

    $this->assertArraySubset([
      'number' => 'ONE',
      'numbers' => [
        'ONE', 'TWO', 'THREE',
      ],
    ], $result['data'], 'Enum plugins accept and return properly.');
  }

  /**
   * Test enumerations generated from annotations.
   */
  public function testEnumAnnotations() {
    $result = $this->executeQueryFile('enums.gql');

    $this->assertArraySubset([
      'character' => 'A',
      'characters' => [
        'A', 'B', 'C',
      ],
    ], $result['data'], 'Annotated enums accept and return properly.');
  }

  /**
   * Test enum type names.
   */
  public function testEnumTypeNames() {
    /** @var \Youshido\GraphQL\Schema\AbstractSchema $schema */
    $schema = \Drupal::service('graphql.schema');
    $types = TypeCollector::collectTypes($schema);
    foreach ($types as $type) {
      if ($type instanceof EnumType && $type->getName() === NULL) {
        $this->fail('Unnamed enum type found.');
      }
    }
  }

}
