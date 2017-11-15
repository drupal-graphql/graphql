<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Cache\Context\ContextCacheKeys;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql\Traits\SchemaProphecyTrait;
use Prophecy\Argument;
use Youshido\GraphQL\Type\Scalar\StringType;

/**
 * Test schema caching.
 *
 * @group graphql
 * @group cache
 */
class SchemaCacheTest extends KernelTestBase {
  use SchemaProphecyTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql'];

  /**
   * Test basic schema caching.
   */
  public function testCacheableSchema() {
    $this->container->getDefinition('graphql.schema_loader')->setShared(FALSE);

    // Prophesize a field with permanent cache.
    $metadata = new CacheableMetadata();
    $metadata->setCacheMaxAge(Cache::PERMANENT);
    $root = $this->prophesizeField('root', new StringType(), $metadata);
    $root->resolve(Argument::any())->willReturn('test');

    /** @var \Prophecy\Prophecy\MethodProphecy $getSchema */
    $schema = $this->createSchema($this->container, $root->reveal());
    $getSchema = $this->injectSchema($schema);

    $this->container->get('graphql.schema_loader')->getSchema('default');
    $getSchema->shouldHaveBeenCalledTimes(1);

    $this->container->get('graphql.schema_loader')->getSchema('default');
    $getSchema->shouldHaveBeenCalledTimes(1);
  }

  /**
   * Test an uncacheable schema.
   */
  public function testUncacheableSchema() {
    $this->container->getDefinition('graphql.schema_loader')->setShared(FALSE);

    // Prophesize an uncacheable field.
    $metadata = new CacheableMetadata();
    $metadata->setCacheMaxAge(0);
    $root = $this->prophesizeField('root', new StringType(), $metadata);
    $root->resolve(Argument::any())->willReturn('test');

    /** @var \Prophecy\Prophecy\MethodProphecy $getSchema */
    $schema = $this->createSchema($this->container, $root->reveal());
    $getSchema = $this->injectSchema($schema);

    $this->container->get('graphql.schema_loader')->getSchema('default');
    $getSchema->shouldHaveBeenCalledTimes(1);

    $this->container->get('graphql.schema_loader')->getSchema('default');
    $getSchema->shouldHaveBeenCalledTimes(2);
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

    // Prophesize an uncacheable field.
    $metadata = new CacheableMetadata();
    $metadata->setCacheContexts(['context']);
    $root = $this->prophesizeField('root', new StringType(), $metadata);
    $root->resolve(Argument::any())->willReturn('test');

    /** @var \Prophecy\Prophecy\MethodProphecy $getSchema */
    $schema = $this->createSchema($this->container, $root->reveal());
    $getSchema = $this->injectSchema($schema);

    $contextKeys->willReturn(new ContextCacheKeys(['a']));
    $this->container->get('graphql.schema_loader')->getSchema('default');
    $getSchema->shouldHaveBeenCalledTimes(1);

    $contextKeys->willReturn(new ContextCacheKeys(['b']));
    $this->container->get('graphql.schema_loader')->getSchema('default');
    $getSchema->shouldHaveBeenCalledTimes(2);
  }

  /**
   * Test tag based schema invalidation.
   */
  public function testTags() {
    $this->container->getDefinition('graphql.schema_loader')->setShared(FALSE);

    // Prophesize an uncacheable field.
    $metadata = new CacheableMetadata();
    $metadata->setCacheTags(['a', 'b']);
    $root = $this->prophesizeField('root', new StringType(), $metadata);
    $root->resolve(Argument::any())->willReturn('test');

    /** @var \Prophecy\Prophecy\MethodProphecy $getSchema */
    $schema = $this->createSchema($this->container, $root->reveal());
    $getSchema = $this->injectSchema($schema);

    $this->container->get('graphql.schema_loader')->getSchema('default');
    $getSchema->shouldHaveBeenCalledTimes(1);

    $this->container->get('cache_tags.invalidator')->invalidateTags(['a']);

    $this->container->get('graphql.schema_loader')->getSchema('default');
    $getSchema->shouldHaveBeenCalledTimes(2);
  }

}
