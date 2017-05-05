<?php

namespace Drupal\Tests\graphql_core\Kernel;

/**
 * Test caching of query results.
 *
 * @group graphql_core
 */
class ResultCacheTest extends GraphQLFileTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql_cache_test',
  ];

  /**
   * Test if the schema is created properly.
   */
  public function testCachedQuery() {
    $first = $this->requestWithQueryFile('cache.gql');
    $second = $this->requestWithQueryFile('cache.gql');

    $this->assertEquals($first, $second, 'Both runs return the same result.');
  }

  /**
   * Test if a tag clear invalidates the cache.
   */
  public function testTagClear() {
    $first = $this->executeQueryFile('cache.gql');
    $this->container->get('cache_tags.invalidator')->invalidateTags(['a']);
    $second = $this->executeQueryFile('cache.gql');

    $this->assertNotEquals($first, $second, 'Clearing the tag invalidated the cache.');
  }

  /**
   * Test if a context update invalidates the cache.
   */
  public function testContextChange() {
    $first = $this->executeQueryFile('cache.gql');
    $this->container->get('cache_context.graphql_test')->setContext(1);
    $second = $this->executeQueryFile('cache.gql');

    $this->assertNotEquals($first, $second, 'Changing the context invalidated the cache.');
  }

}
