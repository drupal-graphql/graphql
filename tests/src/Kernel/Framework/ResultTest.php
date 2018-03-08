<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\graphql\GraphQL\QueryProvider\QueryProviderInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use GraphQL\Server\OperationParams;
use Prophecy\Argument;

/**
 * Test the whole query result pipeline.
 *
 * @group graphql
 */
class ResultTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->mockField('root', [
      'name' => 'root',
      'type' => 'String',
    ], 'test');
  }

  /**
   * Test a simple query result.
   */
  public function testQuery() {
    $result = $this->query('query { root }');
    $this->assertSame(200, $result->getStatusCode());
    $this->assertSame([
      'data' => [
        'root' => 'test',
      ],
    ], json_decode($result->getContent(), TRUE));
  }

  /**
   * Test a persisted query result.
   */
  public function testPersistedQuery() {
    $queryProvider = $this->prophesize(QueryProviderInterface::class);
    $this->container->set('graphql.query_provider', $queryProvider->reveal());

    $queryProvider->getQuery('a', Argument::any())->willReturn('query { root }');

    $result = $this->persistedQuery('a');
    $this->assertSame(200, $result->getStatusCode());
    $this->assertSame([
      'data' => [
        'root' => 'test',
      ],
    ], json_decode($result->getContent(), TRUE));
  }

  /**
   * Test a batched query result.
   */
  public function testBatchedQueries() {
    $queryProvider = $this->prophesize(QueryProviderInterface::class);
    $this->container->set('graphql.query_provider', $queryProvider->reveal());

    $queryProvider->getQuery(Argument::any())->willReturn(NULL);
    $queryProvider->getQuery('a', Argument::any())
      ->willReturn('query { root }');

    $result = $this->batchedQueries([
      ['query' => 'query { root } '],
      ['queryId' => 'a'],
    ]);

    $this->assertSame(200, $result->getStatusCode());
    $this->assertSame([
      [
        'data' => [
          'root' => 'test',
        ],
      ],
      [
        'data' => [
          'root' => 'test',
        ],
      ],
    ], json_decode($result->getContent(), TRUE));
  }

}
