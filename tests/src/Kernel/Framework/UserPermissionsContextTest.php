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
   * Assert user.permissions tag on results.
   */
  public function testUserPermissionsContext() {
    $this->mockField('root', [
      'name' => 'root',
      'type' => 'String',
    ], 'test');

    $result = $this->query('query { root }');
    $this->assertContains('user.permissions', $result->getCacheableMetadata()->getCacheContexts());
  }
}