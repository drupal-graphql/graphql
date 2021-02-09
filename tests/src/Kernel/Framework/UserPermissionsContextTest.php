<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Verify that all queries declare the user.permissions cache context.
 *
 * This is imperative to ensure that authorized queries are not cached
 * and served to unauthorized users.
 *
 * @group graphql
 */
class UserPermissionsContextTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
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
  }

  /**
   * Assert user.permissions tag on results.
   */
  public function testUserPermissionsContext(): void {
    $this->mockResolver('Query', 'root', 'test');
    $this->assertResults('{ root }', [], ['root' => 'test']);
  }

}
