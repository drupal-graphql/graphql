<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Cache\Context\ContextCacheKeys;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Prophecy\Argument;

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
    $this->container->getDefinition('graphql.schema_loader')->setShared(FALSE);

    $this->mockField('root', [
      'id' => 'root',
      'name' => 'root',
      'type' => 'String',
    ], 'test');

    $this->schemaManager
      ->expects(static::once())
      ->method('createInstance')
      ->with(static::anything(), static::anything())
      ->willReturnCallback(function ($id) {
        return $this->mockSchema($id);
      });

    $this->container->get('graphql.schema_loader')->getSchema('default');
    $this->container->get('graphql.schema_loader')->getSchema('default');
  }

  /**
   * Test an uncacheable schema.
   */
  public function testUncacheableSchema() {
    $this->container->getDefinition('graphql.schema_loader')->setShared(FALSE);

    $this->mockField('root', [
      'id' => 'root',
      'name' => 'root',
      'type' => 'String',
      'schema_cache_max_age' => 0,
    ], 'test');

    $this->schemaManager
      ->expects(static::exactly(2))
      ->method('createInstance')
      ->with(static::anything(), static::anything())
      ->willReturnCallback(function ($id) {
        return $this->mockSchema($id);
      });

    $this->container->get('graphql.schema_loader')->getSchema('default');
    $this->container->get('graphql.schema_loader')->getSchema('default');
  }

  /**
   * Test context based schema invalidation.
   */
  public function testContext() {
    $this->container->getDefinition('graphql.schema_loader')->setShared(FALSE);

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

    $this->mockField('root', [
      'id' => 'root',
      'name' => 'root',
      'type' => 'String',
      'schema_cache_contexts' => ['context'],
    ], 'test');

    $this->schemaManager
      ->expects(static::exactly(2))
      ->method('createInstance')
      ->with(static::anything(), static::anything())
      ->willReturnCallback(function ($id) {
        return $this->mockSchema($id);
      });

    $contextKeys->willReturn(new ContextCacheKeys(['a']));
    $this->container->get('graphql.schema_loader')->getSchema('default');

    $contextKeys->willReturn(new ContextCacheKeys(['b']));
    $this->container->get('graphql.schema_loader')->getSchema('default');
  }

  /**
   * Test tag based schema invalidation.
   */
  public function testTags() {
    $this->container->getDefinition('graphql.schema_loader')->setShared(FALSE);

    $this->mockField('root', [
      'id' => 'root',
      'name' => 'root',
      'type' => 'String',
      'schema_cache_tags' => ['a'],
    ], 'test');

    $this->schemaManager
      ->expects(static::exactly(2))
      ->method('createInstance')
      ->with(static::anything(), static::anything())
      ->willReturnCallback(function ($id) {
        return $this->mockSchema($id);
      });

    $this->container->get('graphql.schema_loader')->getSchema('default');

    $this->container->get('cache_tags.invalidator')->invalidateTags(['a']);

    $this->container->get('graphql.schema_loader')->getSchema('default');
  }

}
