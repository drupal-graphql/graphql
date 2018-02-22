<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\graphql\GraphQL\QueryProvider\QueryProviderInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use PhpParser\Node\Arg;
use Prophecy\Argument;

/**
 * Test if query handling respects permissions properly.
 *
 * @group graphql
 */
class PermissionsTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  protected function userPermissions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->mockField('root', [
      'name' => 'root',
      'type' => 'String',
      'secure' => TRUE,
    ], 'test');

    // Set up a query map provider.
    $queryProvider = $this->prophesize(QueryProviderInterface::class);
    $queryProvider->getQuery(Argument::any(), Argument::any())->willReturn(NULL);
    $queryProvider->getQuery('persisted:a', Argument::any())->willReturn('{ root }');

    $this->container->set('graphql.query_provider', $queryProvider->reveal());
  }

  /**
   * Test if a user without permissions doesn't have access to any query.
   */
  public function testNoPermissions() {
    $this->accountProphecy->hasPermission(Argument::any())->willReturn(FALSE);

    // Any query should fail.
    $this->assertEquals(403, $this->query('query')->getStatusCode());
    $this->assertEquals(403, $this->persistedQuery('persisted:a')->getStatusCode());

    $batched = $this->batchedQueries([
      ['query' => '{ root }'],
      ['queryId' => 'persisted:a'],
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
    $this->accountProphecy->hasPermission(Argument::is('execute persisted graphql requests'))->willReturn(TRUE);
    $this->accountProphecy->hasPermission(Argument::not('execute persisted graphql requests'))->willReturn(FALSE);

    // Only persisted queries should work.
    $this->assertEquals(403, $this->query('{ root }')->getStatusCode());
    $this->assertEquals(200, $this->persistedQuery('persisted:a')->getStatusCode());

    $batched = $this->batchedQueries([
      ['query' => '{ root }'],
      ['queryId' => 'persisted:a'],
    ]);

    // If some queries fail, 403 is returned.
    $this->assertEquals(403, $batched->getStatusCode());
  }

  /**
   * Test access to arbitrary queries.
   *
   * The user is allowed to post any queries.
   */
  public function testFullQueryAccess() {
    $this->accountProphecy->hasPermission(Argument::is('execute graphql requests'))->willReturn(TRUE);
    $this->accountProphecy->hasPermission(Argument::not('execute graphql requests'))->willReturn(FALSE);

    // All queries should work.
    $this->assertEquals(200, $this->query('{ root }')->getStatusCode());
    $this->assertEquals(200, $this->persistedQuery('persisted:a')->getStatusCode());

    $batched = $this->batchedQueries([
      ['query' => '{ root }'],
      ['queryId' => 'persisted:a'],
    ]);

    $this->assertEquals(200, $batched->getStatusCode());
    $data = [
      'data' => [
        'root' => 'test',
      ],
    ];
    $this->assertEquals([$data, $data], json_decode($batched->getContent(), TRUE));
  }

}
