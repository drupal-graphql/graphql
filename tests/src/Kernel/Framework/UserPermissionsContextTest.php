<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verify that all queries declare the user.permissions cache context.
 *
 * This is imperative to ensure that authorized queries are not cached
 * and served to unauthorized users.
 *
 * @group graphql
 */
#[Group('graphql')]
#[RunTestsInSeparateProcesses]
#[AllowMockObjectsWithoutExpectations]
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
