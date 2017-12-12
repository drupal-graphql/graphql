<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Session\AccountProxy;
use Drupal\graphql\GraphQL\Execution\QueryProcessor;
use Drupal\graphql\GraphQL\Execution\QueryResult;
use Drupal\graphql\QueryProvider\QueryProviderInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql\Traits\QueryTrait;
use Prophecy\Argument;

/**
 * Test if query handling respects permissions properly.
 *
 * @group graphql
 */
class PermissionsTest extends KernelTestBase {
  use QueryTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql', 'graphql_test'];

  /**
   * The account prophecy.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $account;

  protected function setUp() {
    parent::setUp();
    // Replace the current user with a prophecy.
    $this->account = $this->prophesize(AccountProxy::class);

    // We test permissions. It doesn't matter if the user is anonymous or not.
    $this->account->isAnonymous()->willReturn(TRUE);
    $this->container->set('current_user', $this->account->reveal());

    // Set up a processor that just returns NULL. We just wan't to check access.
    $processor = $this->prophesize(QueryProcessor::class);
    $processor->processQuery(Argument::cetera())
      ->willReturn(new QueryResult('test', new CacheableMetadata()));
    $this->container->set('graphql.query_processor', $processor->reveal());

    // Set up a query map provider.
    $queryProvider = $this->prophesize(QueryProviderInterface::class);
    $queryProvider->getQuery(Argument::any())->willReturn(NULL);
    $queryProvider->getQuery(Argument::allOf(
        Argument::withEntry('id', 'persisted'),
        Argument::withEntry('version', 'a')
    ))->willReturn('persisted');

    $this->container->set('graphql.query_provider', $queryProvider->reveal());
  }

  /**
   * Test if a user without permissions doesn't have access to any query.
   */
  public function testNoPermissions() {
    $this->account->hasPermission(Argument::any())->willReturn(FALSE);

    // Any query should fail.
    $this->assertEquals(403, $this->query('query')->getStatusCode());
    $this->assertEquals(403, $this->persistedQuery('persisted', 'a')->getStatusCode());

    $batched = $this->batchedQueries([
      ['query' => 'query'],
      ['id' => 'persisted', 'version' => 'a'],
    ]);

    // If all batched queries fail, 403 is returned.
    $this->assertEquals(403, $batched->getStatusCode());
  }

  /**
   * Test access to persisted queries.
   *
   * The user is only allowed to access persisted queries, not arbitrary ones.
   */
  public function testPersistedQueryAccess() {
    $this->account->hasPermission(Argument::is('execute persisted graphql requests'))->willReturn(TRUE);
    $this->account->hasPermission(Argument::not('execute persisted graphql requests'))->willReturn(FALSE);

    // Only persisted queries should work.
    $this->assertEquals(403, $this->query('query')->getStatusCode());
    $this->assertEquals(200, $this->persistedQuery('persisted', 'a')->getStatusCode());

    $batched = $this->batchedQueries([
      ['query' => 'query'],
      ['id' => 'persisted', 'version' => 'a'],
    ]);

    // If some queries fail, 200 is returned.
    $this->assertEquals(200, $batched->getStatusCode());
    $this->assertEquals([NULL, 'test'], json_decode($batched->getContent(), TRUE));
  }

  /**
   * Test access to arbitrary queries.
   *
   * The user is allowed to post any queries.
   */
  public function testFullQueryAccess() {
    $this->account->hasPermission(Argument::is('execute graphql requests'))->willReturn(TRUE);
    $this->account->hasPermission(Argument::not('execute graphql requests'))->willReturn(FALSE);

    // All queries should work.
    $this->assertEquals(200, $this->query('query')->getStatusCode());
    $this->assertEquals(200, $this->persistedQuery('persisted', 'a')->getStatusCode());

    $batched = $this->batchedQueries([
      ['query' => 'query'],
      ['id' => 'persisted', 'version' => 'a'],
    ]);

    $this->assertEquals(200, $batched->getStatusCode());
    $this->assertEquals(['test', 'test'], json_decode($batched->getContent(), TRUE));
  }

}