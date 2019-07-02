<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
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
   * Test if a user without permissions doesn't have access to any query.
   */
  public function testNoPermissions() {
    $this->setUpCurrentUser();

    // Any query should fail.
    $this->assertEquals(403, $this->query('query')->getStatusCode());

    $batched = $this->batchedQueries([
      ['query' => '{ root }'],
      ['query' => '{ root }'],
    ]);

    // If all batched queries fail, 403 is returned.
    $this->assertEquals(403, $batched->getStatusCode());
  }

  /**
   * Test access to arbitrary queries.
   *
   * The user is allowed to post any queries.
   */
  public function testFullQueryAccess() {
    $this->setUpCurrentUser([], ["execute {$this->server->id()} arbitrary graphql requests"]);

    // All queries should work.
    $this->assertEquals(200, $this->query('{ root }')->getStatusCode());

    $batched = $this->batchedQueries([
      ['query' => '{ root }'],
      ['query' => '{ root }'],
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
