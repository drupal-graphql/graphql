<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

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

    $schema = <<<GQL
      schema {
        query: Query
      }
      type Query {
        root: String
      }
GQL;

    $this->setUpSchema($schema);

    $this->mockResolver('Query', 'root', 'test');
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
