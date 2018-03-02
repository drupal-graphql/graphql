<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Cache\Context\ContextCacheKeys;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Prophecy\Argument;

/**
 * Test schema caching.
 *
 * @group graphql
 */
class SchemaCacheTest extends GraphQLTestBase {

  /**
   * @var \Prophecy\Prophecy\MethodProphecy
   */
  protected $contextKeys;

  /**
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $contextsManager;

  /**
   * @param \Drupal\Core\DependencyInjection\ContainerBuilder $container
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);

    // Prepare a prophesied context manager.
    $this->contextsManager = $this->prophesize(CacheContextsManager::class);

    // All tokens are valid for this test.
    $this->contextsManager->assertValidTokens(Argument::any())
      ->willReturn(TRUE);

    // Argument patterns that check if the 'context' is in the list.
    $hasContext = Argument::containing('context');
    $hasNotContext = Argument::that(function ($arg) {
      return !in_array('context', $arg);
    });

    // If 'context' is not defined, we return no cache keys.
    $this->contextsManager->convertTokensToKeys($hasNotContext)
      ->willReturn(new ContextCacheKeys([]));

    // Store the method prophecy so we can replace the result on the fly.
    /** @var \Prophecy\Prophecy\MethodProphecy $contextKeys */
    $this->contextKeys = $this->contextsManager->convertTokensToKeys($hasContext);
    if ($definition = $this->container->getDefinition('cache_contexts_manager')) {
      $definition->setFactory([$this, 'contextsManagerFactory']);
    }
  }

  /**
   * @return object
   */
  public function contextsManagerFactory() {
    return $this->contextsManager->reveal();
  }

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
      $schema['types']['QueryRoot']['fields'],
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
      $schema['types']['QueryRoot']['fields'],
      'Schema has not been cached.'
    );

  }

  /**
   * Test context based schema invalidation.
   */
  public function testContext() {

    // Create a first field.
    $this->mockField('foo', [
      'id' => 'foo',
      'name' => 'foo',
      'type' => 'String',
      'schema_cache_contexts' => ['context'],
    ], 'foo');

    // Set a cache context.
    $this->contextKeys->willReturn(new ContextCacheKeys(['a']));
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
      $schema['types']['QueryRoot']['fields'],
      'Schema has not been cached.'
    );

    // Set a different cache context.
    $this->contextKeys->willReturn(new ContextCacheKeys(['b']));

    // Now the new field should appear.
    $schema = $this->introspect();
    $this->assertArrayHasKey(
      'bar',
      $schema['types']['QueryRoot']['fields'],
      'Schema does not contain the new field.'
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
      $schema['types']['QueryRoot']['fields'],
      'Schema has not been cached.'
    );

    // Clear the fields schema cache tag.
    $this->container->get('cache_tags.invalidator')->invalidateTags(['foo']);

    // Now the new field should appear.
    $schema = $this->introspect();
    $this->assertArrayHasKey(
      'bar',
      $schema['types']['QueryRoot']['fields'],
      'Schema does not contain the new field.'
    );
  }

}
