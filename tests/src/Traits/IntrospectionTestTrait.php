<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\graphql\Utility\StringHelper;

/**
 * Trait to retrieve a name-indexed schema to run assertions on it.
 */
trait IntrospectionTestTrait {

  /**
   * Retrieve a name-index schema to easy assert type system plugins.
   *
   * @return array
   *   The name-indexed schema.
   */
  protected function introspect() {
    if (method_exists($this, 'resetStaticCaches')) {
      $this->resetStaticCaches();
    }
    $introspection = $this->container->get('graphql.introspection')->introspect($this->getDefaultSchema());
    $this->indexByName($introspection['data']);
    return $introspection['data']['__schema'];
  }

  /**
   * Assert certain fields in the GraphQL schema.
   *
   * @param array $fields
   *   Array of [ParentType, FieldName, FieldType] triples. Field type can use
   *   the GraphQL type notation.
   * @param bool $invert
   *   Invert the result and check for non-existence instead.
   */
  protected function assertGraphQLFields(array $fields, $invert = FALSE) {
    $schema = $this->introspect();
    foreach ($fields as list($parent, $name, $type)) {
      $this->assertArrayHasKey($parent, $schema['types'], "Type $parent not found.");
      if ($invert) {
        $this->assertArrayNotHasKey($name, $schema['types'][$parent]['fields'], "Field $name found on type $parent.");
        continue;
      }
      $this->assertArrayHasKey($parent, $schema['types'], "Type $parent not found.");
      $this->assertArrayHasKey($name, $schema['types'][$parent]['fields'], "Field $name not found on type $parent.");
      $field = $schema['types'][$parent]['fields'][$name];

      list($type, $decorators) = StringHelper::parseType($type);

      $decorators = array_map(function ($decorator) {
        return $decorator[1];
      }, $decorators);

      if (in_array('listOf', $decorators)) {
        $this->assertEquals('LIST', $field['type']['kind'], "Expected field $name to be a list.");
        $this->assertEquals($type, $field['type']['ofType']['name'], "Expected field $name to be a list of $type.");
      }
      else {
        $this->assertEquals($type, $field['type']['name'], "Expected field $name to be $type.");
      }
    }
  }

  /**
   * Recursively index all sequences by name.
   *
   * @param array $data
   *   The input array.
   *
   * @internal
   */
  private function indexByName(array &$data) {
    if (count(array_filter(array_keys($data), 'is_int')) == count($data)) {
      // This is a list, remap it.
      $data = array_combine(array_map(function ($key, $row) {
        return is_array($row) && isset($row['name']) ? $row['name'] : $key;
      }, array_keys($data), $data), $data);
    }

    foreach (array_keys($data) as $key) {
      if (is_array($data[$key])) {
        $this->indexByName($data[$key]);
      }
    }
  }

}
