<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Cache\Context\ContextCacheKeys;
use Drupal\graphql\GraphQL\Execution\QueryProcessor;
use Drupal\graphql\GraphQL\Execution\QueryResult;
use Drupal\graphql\QueryProvider\QueryProviderInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql\Traits\ByPassAccessTrait;
use Drupal\Tests\graphql\Traits\EnableCliCacheTrait;
use Drupal\Tests\graphql\Traits\QueryTrait;
use Prophecy\Argument;

/**
 * Test query result caching.
 *
 * @group graphql
 * @group cache
 */
class ResultCacheTest extends KernelTestBase {
  use QueryTrait;
  use ByPassAccessTrait;
  use EnableCliCacheTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql', 'graphql_test'];

  /**
   * Check basic result caching.
   */
  public function testCacheableResult() {
    $processor = $this->prophesize(QueryProcessor::class);

    /** @var \Prophecy\Prophecy\MethodProphecy $process */
    $process = $processor->processQuery(Argument::any(), 'cached', Argument::cetera())
      ->willReturn(new QueryResult(NULL, new CacheableMetadata()));

    $this->container->set('graphql.query_processor', $processor->reveal());

    // The first request that is supposed to be cached.
    $this->query('cached');
    $process->shouldHaveBeenCalledTimes(1);

    // This should not invoke the processor a second time.
    $this->query('cached');
    $process->shouldHaveBeenCalledTimes(1);
  }

  /**
   * Verify that uncacheable results are not cached.
   */
  public function testUncacheableResult() {
    $processor = $this->prophesize(QueryProcessor::class);

    // Create an uncacheable cacheability metatdata object.
    $metadata = new CacheableMetadata();
    $metadata->setCacheMaxAge(0);

    /** @var \Prophecy\Prophecy\MethodProphecy $process */
    $process = $processor->processQuery(Argument::any(), 'uncached', Argument::cetera())
      ->willReturn(new QueryResult(NULL, $metadata));

    $this->container->set('graphql.query_processor', $processor->reveal());

    // The first request that is not supposed to be cached.
    $this->query('uncached');
    $process->shouldHaveBeenCalledTimes(1);

    // This should invoke the processor a second time.
    $this->query('uncached');
    $process->shouldHaveBeenCalledTimes(2);
  }

  /**
   * Test if caching properly handles variabels.
   */
  public function testVariables() {
    $processor = $this->prophesize(QueryProcessor::class);

    /** @var \Prophecy\Prophecy\MethodProphecy $process */
    $process = $processor->processQuery(Argument::any(), 'cached', Argument::cetera())
      ->willReturn(new QueryResult(NULL, new CacheableMetadata()));

    $this->container->set('graphql.query_processor', $processor->reveal());

    // This result will be stored in the cache.
    $this->query('cached', ['value' => 'a']);
    $process->shouldHaveBeenCalledTimes(1);

    // This will trigger a new evaluation since it passes different variables.
    $this->query('cached', ['value' => 'b']);
    $process->shouldHaveBeenCalledTimes(2);

    // This should be served from cache.
    $this->query('cached', ['value' => 'a']);
    $process->shouldHaveBeenCalledTimes(2);

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

    // Set up a mocked processor that returns a result with a cache context.
    $processor = $this->prophesize(QueryProcessor::class);

    $metadata = new CacheableMetadata();
    $metadata->addCacheContexts(['context']);

    /** @var \Prophecy\Prophecy\MethodProphecy $process */
    $process = $processor->processQuery(Argument::any(), 'cached', Argument::cetera())
      ->willReturn(new QueryResult(NULL, $metadata));

    $this->container->set('graphql.query_processor', $processor->reveal());

    // Set the context value to 'a'/
    $contextKeys->willReturn(new ContextCacheKeys(['a']));
    // This will be stored in the cache key for context 'a'.
    $this->query('cached');
    $process->shouldHaveBeenCalledTimes(1);

    // Change the context value to 'b'.
    $contextKeys->willReturn(new ContextCacheKeys(['b']));
    // This will be stored in the cache key for context 'b'.
    $this->query('cached');
    $process->shouldHaveBeenCalledTimes(2);

    // Change the context value back to 'a'.
    $contextKeys->willReturn(new ContextCacheKeys(['a']));
    // This will be retrieved from cache for context 'a'.
    $this->query('cached');
    $process->shouldHaveBeenCalledTimes(2);
  }

  /**
   * Test if results cache properly acts on cache tag clears.
   */
  public function testTags() {

    // Set up a mocked processor that returns a result with cache tags.
    $processor = $this->prophesize(QueryProcessor::class);

    $metadata = new CacheableMetadata();
    $metadata->addCacheTags(['a', 'b']);

    /** @var \Prophecy\Prophecy\MethodProphecy $process */
    $process = $processor->processQuery(Argument::any(), 'cached', Argument::cetera())
      ->willReturn(new QueryResult(NULL, $metadata));

    $this->container->set('graphql.query_processor', $processor->reveal());

    // First call that will be cached.
    $this->query('cached');
    $process->shouldHaveBeenCalledTimes(1);

    // Invalidate a tag that is part of the result metadata.
    $this->container->get('cache_tags.invalidator')->invalidateTags(['a']);

    // Another call will invoke the processor a second time.
    $this->query('cached');
    $process->shouldHaveBeenCalledTimes(2);

    // Invalidate a tag that is NOT part of the result metadata.
    $this->container->get('cache_tags.invalidator')->invalidateTags(['c']);

    // Result will be served from cache.
    $this->query('cached');
    $process->shouldHaveBeenCalledTimes(2);
  }

  /**
   * Test batched query caching.
   *
   * Batched queries are split up into kernel subrequests for every single
   * query. Therefore we don't need to test every edge case, but just verify
   * that each of them are cached separately.
   */
  public function testBatchedQueries() {
    $processor = $this->prophesize(QueryProcessor::class);

    /** @var \Prophecy\Prophecy\MethodProphecy $process */
    $process = $processor->processQuery(Argument::cetera())
      ->willReturn(new QueryResult(NULL, new CacheableMetadata()));

    $this->container->set('graphql.query_processor', $processor->reveal());

    $this->batchedQueries([
      // First call: process + 1
      ['query' => 'a', 'variables' => ['value' => 'a']],
      // New query: process + 1
      ['query' => 'b', 'variables' => ['value' => 'a']],
      // New query: process + 1
      ['query' => 'c'],
      // Same query, new variable: process + 1
      ['query' => 'a', 'variables' => ['value' => 'b']],
      // Same query, same variable: process + 0
      ['query' => 'b', 'variables' => ['value' => 'a']],
      // Same query: process + 0
      ['query' => 'c'],
    ]);

    $process->shouldHaveBeenCalledTimes(4);
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
    ))->willReturn('A');

    $queryProvider->getQuery(Argument::allOf(
      Argument::withEntry('version', 'b'),
      Argument::withEntry('id', 'query')
    ))->willReturn('B');

    $processor = $this->prophesize(QueryProcessor::class);
    $this->container->set('graphql.query_processor', $processor->reveal());

    /** @var \Prophecy\Prophecy\MethodProphecy $processA */
    $processA = $processor->processQuery(Argument::any(), 'A', Argument::cetera())
      ->willReturn(new QueryResult(NULL, new CacheableMetadata()));
    /** @var \Prophecy\Prophecy\MethodProphecy $processB */
    $processB = $processor->processQuery(Argument::any(), 'B', Argument::cetera())
      ->willReturn(new QueryResult(NULL, new CacheableMetadata()));

    $this->persistedQuery('query','a');
    $processA->shouldHaveBeenCalledTimes(1);

    $this->persistedQuery('query', 'b');
    $processB->shouldHaveBeenCalledTimes(1);

    $this->persistedQuery('query', 'a');
    $processA->shouldHaveBeenCalledTimes(1);

    $this->persistedQuery('query', 'b', ['value' => 'test']);
    $processB->shouldHaveBeenCalledTimes(2);
  }

}
