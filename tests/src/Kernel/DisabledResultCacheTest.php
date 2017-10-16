<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\graphql\GraphQL\Execution\QueryProcessor;
use Drupal\graphql\GraphQL\Execution\QueryResult;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql\Traits\ByPassAccessTrait;
use Drupal\Tests\graphql\Traits\EnableCliCacheTrait;
use Drupal\Tests\graphql\Traits\QueryTrait;
use Prophecy\Argument;

/**
 * Test disabled result cache.
 *
 * @group graphql
 */
class DisabledResultCacheTest extends KernelTestBase {
  use QueryTrait;
  use ByPassAccessTrait;
  use EnableCliCacheTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql', 'graphql_test'];

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);
    // Set the result_cache parameter to FALSE.
    $parameters = $container->getParameter('graphql.config');
    $parameters['result_cache'] = FALSE;
    $container->setParameter('graphql.config', $parameters);
  }

  /**
   * Test if disabling the result cache has the desired effect.
   */
  public function testDisabledCache() {
    $processor = $this->prophesize(QueryProcessor::class);

    /** @var \Prophecy\Prophecy\MethodProphecy $process */
    $process = $processor->processQuery(Argument::any(), 'cached', Argument::cetera())
      ->willReturn(new QueryResult(NULL, new CacheableMetadata()));

    $this->container->set('graphql.query_processor', $processor->reveal());

    // The first request that is not supposed to be cached.
    $this->query('cached');
    $process->shouldHaveBeenCalledTimes(1);

    // This should invoke the processor a second time.
    $this->query('cached');
    $process->shouldHaveBeenCalledTimes(2);
  }

}
