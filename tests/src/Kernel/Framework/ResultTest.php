<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\graphql\GraphQL\QueryProvider\QueryProviderInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use GraphQL\Server\OperationParams;
use Prophecy\Argument;
use Drupal\graphql\GraphQL\ResolverBuilder;

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

    $gql_schema = <<<GQL
      schema {
        query: Query
      }
      type Query {
        root: String
      }
GQL;
    $this->setUpSchema($gql_schema, $this->getDefaultSchema());
    $builder = new ResolverBuilder();

    $this->mockField('root', [
      'name' => 'root',
      'type' => 'String',
      'parent' => 'Query'
    ], $builder->fromValue('test'));
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
   * Test a batched query result.
   */
  public function testBatchedQueries() {
    $result = $this->batchedQueries([
      ['query' => 'query { root } '],
      ['query' => 'query { root }'],
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
