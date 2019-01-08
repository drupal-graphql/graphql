<?php

namespace Drupal\Tests\graphql\Unit;

use Drupal\graphql\GraphQL\QueryProvider\QueryProvider;
use Drupal\graphql\GraphQL\QueryProvider\QueryProviderInterface;
use Drupal\Tests\UnitTestCase;
use GraphQL\Server\OperationParams;

/**
 * Unit tests for the QueryProvider class.
 *
 * @group graphql
 */
class QueryProviderTest extends UnitTestCase {

  /**
   * @covers \Drupal\graphql\GraphQL\QueryProvider\QueryProvider::getQuery
   */
  public function testGetQuery() {
    $operationParams = new OperationParams();
    $queryProviderA = $this->prophesize(QueryProviderInterface::class);
    $queryProviderA->getQuery("", $operationParams)
      ->willReturn('query_A');
    $queryProviderB = $this->prophesize(QueryProviderInterface::class);
    $queryProviderB->getQuery("", $operationParams)
      ->willReturn('query_B');

    $queryProvider = new QueryProvider();
    $queryProvider->addQueryProvider($queryProviderA->reveal(), 10);
    $queryProvider->addQueryProvider($queryProviderB->reveal(), 15);
    $query = $queryProvider->getQuery('', $operationParams);
    $this->assertEquals('query_B', $query);

    $queryProvider = new QueryProvider();
    $queryProvider->addQueryProvider($queryProviderA->reveal(), 15);
    $queryProvider->addQueryProvider($queryProviderB->reveal(), 10);
    $query = $queryProvider->getQuery('', $operationParams);
    $this->assertEquals('query_A', $query);

    $queryProvider = new QueryProvider();
    $this->assertNull($queryProvider->getQuery('', $operationParams));
  }

}
