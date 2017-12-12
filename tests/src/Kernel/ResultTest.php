<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql\QueryProvider\QueryProviderInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql\Traits\ByPassAccessTrait;
use Drupal\Tests\graphql\Traits\QueryTrait;
use Drupal\Tests\graphql\Traits\SchemaProphecyTrait;
use Prophecy\Argument;
use Youshido\GraphQL\Schema\Schema;
use Youshido\GraphQL\Type\Scalar\StringType;

/**
 * Test the whole query result pipeline.
 *
 * @group graphql
 * @group cache
 */
class ResultTest extends KernelTestBase {
  use QueryTrait;
  use ByPassAccessTrait;
  use SchemaProphecyTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $root = $this->prophesizeField('root', new StringType());
    $root->resolve(Argument::cetera())->willReturn('test');

    $schema = $this->createSchema($this->container, $root->reveal());
    $this->injectSchema($schema);
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

    $queryProvider->getQuery(Argument::allOf(
      Argument::withEntry('version', 'b'),
      Argument::withEntry('id', 'a')
    ))->willReturn('query { root }');

    $result = $this->persistedQuery('a', 'b');
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
    $queryProvider->getQuery(Argument::allOf(
      Argument::withEntry('version', 'b'),
      Argument::withEntry('id', 'a')
    ))->willReturn('query { root }');

    $result = $this->batchedQueries([
      ['query' => 'query { root } '],
      ['id' => 'a', 'version' => 'b'],
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
