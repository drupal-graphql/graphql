<?php

namespace Drupal\Tests\graphql_core\Kernel;

/**
 * Test caching of query results.
 */
class ResultCacheTest extends GraphQLFileTestBase {

  public static $modules = [
    'graphql_cache_test',
  ];

  /**
   * Test if the schema is created properly.
   */
  public function testCachedQuery() {
    $first = $this->executeQueryFile('cache/cached.gql');
    $second = $this->executeQueryFile('cache/cached.gql');

    $this->assertEquals($first, $second, 'Both runs return the same result.');
  }

  /**
   * Test if the schema is created properly.
   */
  public function testUncachedQuery() {
    $first = $this->executeQueryFile('cache/uncached.gql');
    $second = $this->executeQueryFile('cache/uncached.gql');

    $this->assertNotEquals($first, $second, 'The second result was not retrieved from cache.');
  }

  /**
   * Test if a tag clear invalidates the cache.
   */
  public function testTagClear() {
    /** @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache */
    $cache = $this->container->get('cache.graphql_response');

    $first = $this->executeQueryFile('cache/cached.gql');
    $cache->invalidateTags(['graphql_test']);
    $second = $this->executeQueryFile('cache/cached.gql');

    $this->assertNotEquals($first, $second, 'Clearing the tag invalidated the cache.');
  }

  /**
   * Test if a context update invalidates the cache.
   */
  public function testContextChange() {
    $first = $this->executeQueryFile('cache/cached.gql');
    $this->container->get('cache_context.graphql_test')->setContext(1);
    $second = $this->executeQueryFile('cache/cached.gql');

    $this->assertNotEquals($first, $second, 'Changing the context invalidated the cache.');
  }

}
