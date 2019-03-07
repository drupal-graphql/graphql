<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\graphql\GraphQL\Buffers\BufferBase;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Zend\Stdlib\ArrayObject;
use GraphQL\Deferred;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerCallable;

/**
 * Test batched field resolving.
 *
 * @group graphql
 */
class BufferedFieldTest extends GraphQLTestBase {

  /**
   * Test if the schema is created properly.
   */
  public function testBatchedFields() {
    $gql_schema = <<<GQL
      schema {
        query: Query
      }
      type Query {
        users(uids: [String]): [User]
      }

      type User {
        name: String
        friends: [User]
        foe: User
      }
GQL;
    $this->setUpSchema($gql_schema, $this->getDefaultSchema());
    $builder = new ResolverBuilder();

    $buffer = $this->getMockBuilder(BufferBase::class)
      ->setMethods(['resolveBufferArray'])
      ->getMock();

    $users = [
      'a' => [
        'name' => 'A',
        'friends' => ['c', 'd'],
      ],
      'b' => [
        'name' => 'B',
        'foe' => 'e',
      ],
      'c' => ['name' => 'C'],
      'd' => ['name' => 'D'],
      'e' => ['name' => 'E'],
    ];

    $buffer->expects(static::exactly(2))
      ->method('resolveBufferArray')
      ->willReturnCallback(function ($items) use ($users) {
        return array_map(function ($item) use ($users) {
          return $users[$item['uid']];
        }, $items);
      });

    $this->mockField('users', [
      'name' => 'users',
      'type' => '[User]',
      'parent' => 'Query',
    ], function ($value, $args, ResolveContext $context, ResolveInfo $info) use ($buffer) {
        $resolvers = array_map(function ($uid) use ($buffer) {
          return $buffer->createBufferResolver(new ArrayObject(['uid' => $uid]));
        }, $args['uids']);

        return new Deferred(function () use ($resolvers) {
          $result = [];
          foreach ($resolvers as $resolver) {
            $result[] = $resolver();
          }
          return $result;
        });
      }
    );

    $this->mockField('name', [
      'name' => 'name',
      'type' => 'String',
      'parent' => 'User',
    ], $builder->compose(
        $builder->fromParent(),
        new DataProducerCallable(function ($value, $args, ResolveContext $context, ResolveInfo $info) {
          return $value['name'];
        })
      )
    );

    $this->mockField('friends', [
      'name' => 'friends',
      'type' => '[User]',
      'parent' => 'User',
    ], function ($value, $args, ResolveContext $context, ResolveInfo $info) use ($buffer) {
        $resolvers = array_map(function ($uid) use ($buffer) {
          return $buffer->createBufferResolver(new ArrayObject(['uid' => $uid]));
        }, $value['friends']);

        return new Deferred(function () use ($resolvers) {
          $result = [];
          foreach ($resolvers as $resolver) {
            $result[] = $resolver();
          }
          return $result;
        });
      }
    );

    $this->mockField('foe', [
      'name' => 'foe',
      'type' => 'User',
      'parent' => 'User',
    ], function ($value, $args, ResolveContext $context, ResolveInfo $info) use ($buffer) {
        $resolver = $buffer->createBufferResolver(new ArrayObject(['uid' => $value['foe']]));

        return new Deferred(function () use ($resolver) {
          return $resolver();
        });
      }
    );

    $query = $this->getQueryFromFile('batched.gql');
    $metadata = $this->defaultCacheMetaData();
    $this->assertResults($query, [], [
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
    ], $metadata);
  }

}
