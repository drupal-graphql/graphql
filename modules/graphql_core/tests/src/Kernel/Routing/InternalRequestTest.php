<?php

namespace Drupal\Tests\graphql_core\Kernel\Routing;

use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;

/**
 * Test internal requests.
 *
 * @group graphql_core
 */
class InternalRequestTest extends GraphQLFileTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql_requests_test', 'graphql_core'];

  /**
   * Test internal requests.
   */
  public function testInternalRequests() {
    $result = $this->executeQueryFile('internal_requests.gql');

    $this->assertEquals(200, $result['data']['ok']['request']['code']);
    $this->assertContains('<p>Test</p>', $result['data']['ok']['request']['content']);

    $this->assertEquals(302, $result['data']['redirect']['request']['code']);
    $this->assertEquals('/graphql-request/test', $result['data']['redirect']['request']['location']);
  }

}
