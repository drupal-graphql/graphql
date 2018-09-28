<?php

namespace Drupal\Tests\graphql_core\Kernel\Routing;

use Drupal\Tests\graphql_core\Kernel\GraphQLCoreTestBase;

/**
 * Test internal requests.
 *
 * @group graphql_core
 */
class InternalRequestTest extends GraphQLCoreTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql_core', 'graphql_requests_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
  }

  /**
   * Test internal requests.
   */
  public function testInternalRequests() {
    $metadata = $this->defaultCacheMetaData();

    $this->assertResults($this->getQueryFromFile('internal_requests.gql'), [], [
      'ok' => [
        'request' => [
          'code' => 200,
          'content' => '<p>Test</p>',
        ],
      ],
      'redirect' => [
        'request' => [
          'code' => 302,
          'location' => '/graphql-request/test',
        ],
      ],
    ], $metadata);
  }

}
