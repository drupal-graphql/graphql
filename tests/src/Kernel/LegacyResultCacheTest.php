<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;

/**
 * Test caching of query results.
 *
 * @group graphql
 */
class LegacyResultCacheTest extends GraphQLFileTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql_cache_test',
  ];

  /**
   * Test if the result is cached properly.
   */
  public function testCachedQuery() {
    $first = $this->requestWithQueryFile('cache.gql');
    $second = $this->requestWithQueryFile('cache.gql');

    $this->assertEquals($first, $second, 'Both runs return the same result.');
  }

  /**
   * Test if the result is cached properly for root fields.
   */
  public function testRootFieldCachedQuery() {
    $first = $this->requestWithQueryFile('cache_root_field.gql');
    $second = $this->requestWithQueryFile('cache_root_field.gql');

    $this->assertEquals($first, $second, 'Both runs return the same result.');
  }

  /**
   * Test if a tag clear invalidates the cache.
   */
  public function testTagClear() {
    $first = $this->requestWithQueryFile('cache.gql');
    $this->container->get('cache_tags.invalidator')->invalidateTags(['a']);
    $second = $this->requestWithQueryFile('cache.gql');

    $this->assertNotEquals($first, $second, 'Clearing the tag invalidated the cache.');
  }

  /**
   * Test if a tag clear invalidates the cache of root fields.
   */
  public function testRootFieldTagClear() {
    $first = $this->requestWithQueryFile('cache_root_field.gql');
    $this->container->get('cache_tags.invalidator')->invalidateTags(['c']);
    $second = $this->requestWithQueryFile('cache_root_field.gql');

    $this->assertNotEquals($first, $second, 'Clearing the tag invalidated the cache.');
  }

  /**
   * Test if a context update invalidates the cache.
   */
  public function testContextChange() {
    $first = $this->requestWithQueryFile('cache.gql');
    $this->container->get('cache_context.graphql_test')->setContext(1);
    $second = $this->requestWithQueryFile('cache.gql');

    $this->assertNotEquals($first, $second, 'Changing the context invalidated the cache.');
  }

  /**
   * Test if a context update invalidates the cache of root fields.
   */
  public function testRootFieldContextChange() {
    $first = $this->requestWithQueryFile('cache_root_field.gql');
    $this->container->get('cache_context.graphql_test_root_field')->setContext(1);
    $second = $this->requestWithQueryFile('cache_root_field.gql');

    $this->assertNotEquals($first, $second, 'Changing the context invalidated the cache.');
  }

}
