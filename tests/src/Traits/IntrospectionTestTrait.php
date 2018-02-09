<?php

namespace Drupal\Tests\graphql\Traits;

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
    $introspection = $this->container->get('graphql.introspection')->introspect($this->getDefaultSchema());
    $this->indexByName($introspection['data']);
    return $introspection['data']['__schema'];
  }

  /**
   * Recursively index all sequences by name.
   *
   * @param array $data
   *   The input array.
   *
   * @internal
   */
  protected function indexByName(&$data) {
    if (!is_array($data)) {
      return;
    }

    if (count(array_filter(array_keys($data), 'is_int')) == count($data)) {
      // This is a list, remap it.
      $data = array_combine(array_map(function ($row) {
        return $row['name'];
      }, $data), $data);
    }

    foreach (array_keys($data) as $key) {
      $this->indexByName($data[$key]);
    }
  }
}