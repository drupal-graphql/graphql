<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test schema caching.
 *
 * @group graphql
 */
class DisabledSchemaCacheTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);
    $config = $container->getParameter('graphql.config');
    $container->setParameter('graphql.config', ['development' => TRUE] + $config);
  }

  /**
   * Test disabled schema caching without cache metadata.
   */
  public function testDisabledCacheWithoutCacheMetadata() {
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

    // Run introspect again, the new field should appear immediately.
    $schema = $this->introspect();
    $this->assertArrayHasKey(
      'bar',
      $schema['types']['Query']['fields'],
      'Schema does not contain the new field.'
    );
  }

  /**
   * Test disabled schema caching with cache metadata.
   */
  public function testDisabledCacheWithCacheMetadata() {
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

    // Run introspect again, the new field should appear immediately.
    $schema = $this->introspect();
    $this->assertArrayHasKey(
      'bar',
      $schema['types']['Query']['fields'],
      'Schema does not contain the new field.'
    );
  }

}
