<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test schema caching.
 *
 * @group graphql
 */
class SchemaCacheTest extends GraphQLTestBase {

  /**
   * Test basic schema caching.
   */
  public function testCacheableSchema() {
    // Create a first field.
    $this->mockField('foo', [
      'id' => 'foo',
      'name' => 'foo',
      'type' => 'String',
    ], 'foo');

    // Run introspect to populate the schema cache.
    $this->introspect();

    // Add another field.
    $this->mockField('bar', [
      'id' => 'bar',
      'name' => 'bar',
      'type' => 'String',
    ], 'bar');

    // Run introspect again, the new field should not appear immediately.
    $schema = $this->introspect();
    $this->assertArrayNotHasKey(
      'bar',
      $schema['types']['Query']['fields'],
      'Schema has not been cached.'
    );
  }

  /**
   * Test an uncacheable schema.
   */
  public function testUncacheableSchema() {
    // Create a first field.
    $this->mockField('foo', [
      'id' => 'foo',
      'name' => 'foo',
      'type' => 'String',
      'schema_cache_max_age' => 0,
    ], 'foo');

    // Run introspect to populate the schema cache.
    $this->introspect();

    // Add another field.
    $this->mockField('bar', [
      'id' => 'bar',
      'name' => 'bar',
      'type' => 'String',
    ], 'bar');

    // Run introspect again, the new field should appear immediately.
    $schema = $this->introspect();
    $this->assertArrayHasKey(
      'bar',
      $schema['types']['Query']['fields'],
      'Schema has not been cached.'
    );

  }

  /**
   * Test tag based schema invalidation.
   */
  public function testTags() {
    // Create a first field.
    $this->mockField('foo', [
      'id' => 'foo',
      'name' => 'foo',
      'type' => 'String',
      'schema_cache_tags' => ['foo'],
    ], 'foo');

    // Run introspect to populate the schema cache.
    $this->introspect();

    // Add another field.
    $this->mockField('bar', [
      'id' => 'bar',
      'name' => 'bar',
      'type' => 'String',
    ], 'bar');

    // Run introspect again, the new field should not appear immediately.
    $schema = $this->introspect();
    $this->assertArrayNotHasKey(
      'bar',
      $schema['types']['Query']['fields'],
      'Schema has not been cached.'
    );

    // Clear the fields schema cache tag.
    $this->container->get('cache_tags.invalidator')->invalidateTags(['foo']);

    // Now the new field should appear.
    $schema = $this->introspect();
    $this->assertArrayHasKey(
      'bar',
      $schema['types']['Query']['fields'],
      'Schema does not contain the new field.'
    );
  }

}
