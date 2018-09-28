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
  public function testCaching() {
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
   * Test tag based schema invalidation.
   */
  public function testCachingWithInvalidation() {
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

    $this->container->get('cache_tags.invalidator')->invalidateTags(['graphql']);

    // Now the new field should appear.
    $schema = $this->introspect();
    $this->assertArrayHasKey(
      'bar',
      $schema['types']['Query']['fields'],
      'Schema does not contain the new field.'
    );
  }

}
