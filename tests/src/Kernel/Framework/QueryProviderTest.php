<?php

namespace Drupal\Tests\graphql\Kernel\Framework;


use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Tests the entire query result pipeline when using persisted queries.
 *
 * @group graphql
 */
class QueryProviderTest extends GraphQLTestBase {

  public static $modules = [
    'graphql_query_provider_test',
  ];

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
        field_one: String
        field_two: String
      }
GQL;
    $this->setUpSchema($gql_schema, $this->getDefaultSchema());
    $builder = new ResolverBuilder();

    $this->mockField('field_one', [
      'name' => 'field_one',
      'type' => 'String',
      'parent' => 'Query'
    ], $builder->fromValue('first field'));
    $this->mockField('field_two', [
      'name' => 'field_two',
      'type' => 'String',
      'parent' => 'Query'
    ], $builder->fromValue('second field'));
  }

  /**
   * Test a simple query result.
   *
   * @dataProvider testQueryProvider
   */
  public function testQuery($queryId, $expected) {
    $result = $this->query(NULL, NULL, [], $queryId);
    $this->assertSame(200, $result->getStatusCode());
    $this->assertSame($expected, json_decode($result->getContent(), TRUE));
  }

  /**
   * Data provider for testQuery
   * @return array
   */
  public function testQueryProvider() {
    return[
      [
        'query_1',
        [
          'data' => [
            'field_one' => 'first field',
          ],
        ],
      ],
      [
        'query_2',
        [
          'data' => [
            'field_two' => 'second field',
          ],
        ],
      ],
    ];
  }

}
