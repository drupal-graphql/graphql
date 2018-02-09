<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Cache\Context\ContextCacheKeys;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\QueryProvider\QueryProviderInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Prophecy\Argument;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Test query result caching.
 *
 * @group graphql
 */
class ResultCacheTest extends GraphQLTestBase {

  /**
   * Check basic result caching.
   */
  public function testCacheableResult() {
    $field = $this->mockField('root', [
      'id' => 'root',
      'name' => 'root',
      'type' => 'String',
    ]);

    $field
      ->expects(static::once())
      ->method('resolveValues')
      ->willReturnCallback(function () {
        yield 'test';
      });

    // The first request that is supposed to be cached.
    $this->query('{ root }');

    // This should *not* invoke the processor a second time.
    $this->query('{ root }');
  }

  /**
   * Verify that uncacheable results are not cached.
   */
  public function testUncacheableResult() {
    $field = $this->mockField('root', [
      'id' => 'root',
      'name' => 'root',
      'type' => 'String',
    ]);

    $callback = function () {
      yield (new CacheableValue('test'))->mergeCacheMaxAge(0);
    };

    $field
      ->expects(static::exactly(2))
      ->method('resolveValues')
      ->will($this->toBoundPromise($callback, $field));

    // The first request that is not supposed to be cached.
    $this->query('{ root }');

    // This should invoke the processor a second time.
    $this->query('{ root }');
  }

  /**
   * Verify that fields with uncacheable annotations are not cached.
   */
  public function testUncacheableResultAnnotation() {
    $field = $this->mockField('root', [
      'id' => 'root',
      'name' => 'root',
      'type' => 'String',
      'response_cache_max_age' => 0,
    ]);

    $field
      ->expects(static::exactly(2))
      ->method('resolveValues')
      ->willReturnCallback(function () {
        yield 'test';
      });

    // The first request that is not supposed to be cached.
    $this->query('{ root }');

    // This should invoke the processor a second time.
    $this->query('{ root }');
  }

  /**
   * Test if caching properly handles variabels.
   */
  public function testVariables() {
    $field = $this->mockField('root', [
      'id' => 'root',
      'name' => 'root',
      'type' => 'String',
    ]);

    $field
      ->expects(static::exactly(2))
      ->method('resolveValues')
      ->willReturnCallback(function () {
        yield 'test';
      });

    // This result will be stored in the cache.
    $this->query('{ root }', ['value' => 'a']);

    // This will trigger a new evaluation since it passes different variables.
    $this->query('{ root }', ['value' => 'b']);

    // This should be served from cache.
    $this->query('{ root }', ['value' => 'a']);
  }

  /**
   * Test if changing test context's trigger re-evaluations.
   */
  public function testContext() {
    // Prepare a prophesied context manager.
    $contextManager = $this->prophesize(CacheContextsManager::class);
    $this->container->set('cache_contexts_manager', $contextManager->reveal());

    // All tokens are valid for this test.
    $contextManager->assertValidTokens(Argument::any())
      ->willReturn(TRUE);

    // Argument patterns that check if the 'context' is in the list.
    $hasContext = Argument::containing('context');
    $hasNotContext = Argument::that(function ($arg) {
      return !in_array('context', $arg);
    });

    // If 'context' is not defined, we return no cache keys.
    $contextManager->convertTokensToKeys($hasNotContext)
      ->willReturn(new ContextCacheKeys([]));

    // Store the method prophecy so we can replace the result on the fly.
    /** @var \Prophecy\Prophecy\MethodProphecy $contextKeys */
    $contextKeys = $contextManager->convertTokensToKeys($hasContext);

    $field = $this->mockField('root', [
      'id' => 'root',
      'name' => 'root',
      'type' => 'String',
      'response_cache_contexts' => ['context'],
    ]);

    $field
      ->expects(static::exactly(2))
      ->method('resolveValues')
      ->willReturnCallback(function () {
        yield 'test';
      });

    // Set the context value to 'a'/
    $contextKeys->willReturn(new ContextCacheKeys(['a']));
    // This will be stored in the cache key for context 'a'.
    $this->query('{ root }');

    // Change the context value to 'b'.
    $contextKeys->willReturn(new ContextCacheKeys(['b']));
    // This will be stored in the cache key for context 'b'.
    $this->query('{ root }');

    // Change the context value back to 'a'.
    $contextKeys->willReturn(new ContextCacheKeys(['a']));
    // This will be retrieved from cache for context 'a'.
    $this->query('{ root }');
  }

  /**
   * Test if results cache properly acts on cache tag clears.
   */
  public function testTags() {
    $field = $this->mockField('root', [
      'id' => 'root',
      'name' => 'root',
      'type' => 'String',
      'response_cache_tags' => ['a', 'b'],
    ]);

    $field
      ->expects(static::exactly(2))
      ->method('resolveValues')
      ->willReturnCallback(function () {
        yield 'test';
      });

    // First call that will be cached.
    $this->query('{ root }');

    // Invalidate a tag that is part of the result metadata.
    $this->container->get('cache_tags.invalidator')->invalidateTags(['a']);

    // Another call will invoke the processor a second time.
    $this->query('{ root }');

    // Invalidate a tag that is NOT part of the result metadata.
    $this->container->get('cache_tags.invalidator')->invalidateTags(['c']);

    // Result will be served from cache.
    $this->query('{ root }');
  }

  /**
   * Test batched query caching.
   *
   * Batched queries are split up into kernel subrequests for every single
   * query. Therefore we don't need to test every edge case, but just verify
   * that each of them are cached separately.
   */
  public function testBatchedQueries() {
    $a = $this->mockField('a', [
      'id' => 'a',
      'name' => 'a',
      'type' => 'String',
    ]);

    $a
      ->expects(static::exactly(2))
      ->method('resolveValues')
      ->willReturnCallback(function () {
        yield 'a';
      });

    $b = $this->mockField('b', [
      'id' => 'b',
      'name' => 'b',
      'type' => 'String',
    ]);

    $b
      ->expects(static::exactly(1))
      ->method('resolveValues')
      ->willReturnCallback(function () {
        yield 'b';
      });

    $c = $this->mockField('c', [
      'id' => 'c',
      'name' => 'c',
      'type' => 'String',
    ]);

    $c
      ->expects(static::exactly(1))
      ->method('resolveValues')
      ->willReturnCallback(function () {
        yield 'c';
      });


    $this->batchedQueries([
      ['query' => '{ a }', 'variables' => ['value' => 'a']],
      ['query' => '{ b }', 'variables' => ['value' => 'a']],
      ['query' => '{ c }'],
      ['query' => '{ a }', 'variables' => ['value' => 'b']],
      ['query' => '{ b }', 'variables' => ['value' => 'a']],
      ['query' => '{ c }'],
    ]);

  }

  /**
   * Test persisted query handling.
   *
   * Ensure caching properly handles different query map versions of the same
   * query.
   */
  public function testPersistedQuery() {
    $queryProvider = $this->prophesize(QueryProviderInterface::class);
    $this->container->set('graphql.query_provider', $queryProvider->reveal());

    $queryProvider->getQuery(Argument::allOf(
      Argument::withEntry('version', 'a'),
      Argument::withEntry('id', 'query')
    ))->willReturn('{ a }');

    $queryProvider->getQuery(Argument::allOf(
      Argument::withEntry('version', 'b'),
      Argument::withEntry('id', 'query')
    ))->willReturn('{ b }');

    $field = $this->mockField('a', [
      'id' => 'a',
      'name' => 'a',
      'type' => 'String',
    ]);

    $field
      ->expects(static::exactly(1))
      ->method('resolveValues')
      ->willReturnCallback(function () {
        yield 'a';
      });

    $field = $this->mockField('b', [
      'id' => 'b',
      'name' => 'b',
      'type' => 'String',
    ]);

    $field
      ->expects(static::exactly(2))
      ->method('resolveValues')
      ->willReturnCallback(function () {
        yield 'b';
      });

    $this->persistedQuery('query', 'a');
    $this->persistedQuery('query', 'b');
    $this->persistedQuery('query', 'a');
    $this->persistedQuery('query', 'b', ['value' => 'test']);
  }

}
