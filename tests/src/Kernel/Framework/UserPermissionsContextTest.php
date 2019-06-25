<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\graphql\GraphQL\ResolverBuilder;

/**
 * Verify that all queries declare the user.permissions cache context.
 *
 * This is imperative to ensure that authorized queries are not cached
 * and served to unauthorized users.
 *
 * @group graphql
 */
class UserPermissionsContextTest extends GraphQLTestBase {

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
  }

  /**
   * Assert user.permissions tag on results.
   */
  public function testUserPermissionsContext() {
    $this->mockResolver('Query', 'root', 'test');

    $metadata = (new CacheableMetadata())->addCacheContexts(['user.permissions']);
    $this->assertResults('{ root }', [], ['root' => 'test'], $metadata);
  }
}
