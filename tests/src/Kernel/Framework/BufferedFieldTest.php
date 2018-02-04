<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\graphql\GraphQL\Buffers\BufferBase;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Zend\Stdlib\ArrayObject;

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

    $this->mockType('user', [
      'name' => 'User',
    ]);

    $this->mockField('users', [
      'name' => 'users',
      'type' => '[User]',
      'arguments' => [
        'uids' => '[String]',
      ],
    ], function ($value, $args) use ($buffer) {

      $resolvers = array_map(function ($uid) use ($buffer) {
        return $buffer->createBufferResolver(new ArrayObject(['uid' => $uid]));
      }, $args['uids']);

      return function () use ($resolvers) {
        foreach ($resolvers as $resolver) {
          yield $resolver();
        }
      };

    });

    $this->mockField('name', [
      'name' => 'name',
      'type' => 'String',
      'parents' => ['User'],
    ], function ($value) {
      yield $value['name'];
    });

    $this->mockField('friends', [
      'name' => 'friends',
      'type' => '[User]',
      'parents' => ['User'],
    ], function ($value) use ($buffer) {
      $resolvers = array_map(function ($uid) use ($buffer) {
        return $buffer->createBufferResolver(new ArrayObject(['uid' => $uid]));
      }, $value['friends']);

      return function () use ($resolvers) {
        foreach ($resolvers as $resolver) {
          yield $resolver();
        }
      };
    });

    $this->mockField('foe', [
      'name' => 'foe',
      'type' => 'User',
      'parents' => ['User'],
    ], function ($value) use ($buffer) {
      $resolver = $buffer->createBufferResolver(new ArrayObject(['uid' => $value['foe']]));

      return function () use ($resolver) {
        yield $resolver();
      };
    });

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
