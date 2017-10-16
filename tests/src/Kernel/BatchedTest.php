<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql_batched_test\UserDataBaseInterface;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;

/**
 * Test batched field resolving.
 *
 * @group graphql
 */
class BatchedTest extends GraphQLFileTestBase {
  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  public static $modules = [
    'graphql_batched_test',
  ];

  /**
   * Test if the schema is created properly.
   */
  public function testBatchedFields() {
    $database = $this->prophesize(UserDataBaseInterface::class);

    $database->fetchUsers(['a', 'b'])->willReturn([
      'a' => [
        'name' => 'A',
        'friends' => ['c', 'd'],
      ],
      'b' => [
        'name' => 'B',
        'foe' => 'e',
      ],
    ])->shouldBeCalledTimes(1);

    $database->fetchUsers(['c', 'd', 'e'])->willReturn([
      'c' => ['name' => 'C'],
      'd' => ['name' => 'D'],
      'e' => ['name' => 'E'],
    ])->shouldBeCalledTimes(1);

    $this->container->set('graphql_batched_test.user_database', $database->reveal());

    $result = $this->executeQueryFile('batched.gql');
    $this->assertEquals([
      'a' => [
        [
          'name' => 'A',
          'friends' => [
            ['name' => 'C'],
            ['name' => 'D'],
          ],
        ],
      ],
      'b' => [
        [
          'name' => 'B',
          'foe' => ['name' => 'E'],
        ],
      ],
    ], $result['data']);
  }

}
