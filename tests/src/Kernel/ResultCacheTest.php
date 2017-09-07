<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\graphql\QueryProcessor;
use Drupal\graphql\QueryResult;
use Drupal\KernelTests\KernelTestBase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test query result caching.
 *
 * @group graphql
 */
class ResultCacheTest extends KernelTestBase  {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Disable the cli deny policy because we actually want caching on cli
    // when kernel testing it.
    $cliPolicy = $this->prophesize(RequestPolicyInterface::class);
    $cliPolicy->check(Argument::cetera())->willReturn(NULL);
    $this->container->set('graphql.request_policy.deny_command_line', $cliPolicy->reveal());

    // Replace the current user with one that is allowed to do GraphQL requests.
    $user = $this->prophesize(AccountProxyInterface::class);
    $user->hasPermission('execute graphql requests')
      ->willReturn(AccessResult::allowed());
    $user->isAnonymous()->willReturn(TRUE);
    $this->container->set('current_user', $user->reveal());
  }

  /**
   * @param $query
   * @param array $variables
   */
  protected function query($query, $variables = []) {
    $this->container->get('http_kernel')->handle(Request::create('/graphql', 'GET', [
      'query' => $query
    ]));
  }

  /**
   * Check basic result caching.
   */
  public function testCachableResult() {
    $processor = $this->prophesize(QueryProcessor::class);

    $processor->processQuery('cached', Argument::cetera())
      ->shouldBeCalledTimes(1)
      ->willReturn(new QueryResult(NULL, new CacheableMetadata()));

    $this->container->set('graphql.query_processor', $processor->reveal());

    // The first request that is supposed to be cached.
    $this->query('cached');
    // This should not invoke the processor a second time.
    $this->query('cached');
  }

  /**
   * Verify that uncachable results are not cached.
   */
  public function testUncachableResult() {
    $processor = $this->prophesize(QueryProcessor::class);

    // Create an uncachable cacheability metatdata object.
    $metadata = new CacheableMetadata();
    $metadata->setCacheMaxAge(0);

    $processor->processQuery('uncached', Argument::cetera())
      ->shouldBeCalledTimes(2)
      ->willReturn(new QueryResult(NULL, $metadata));

    $this->container->set('graphql.query_processor', $processor->reveal());

    // The first request that is not supposed to be cached.
    $this->query('uncached');
    // This should invoke the processor a second time.
    $this->query('uncached');
  }

}
