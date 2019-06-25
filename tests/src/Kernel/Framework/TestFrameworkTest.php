<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\Core\Cache\CacheableDependencyInterface;

/**
 * Test the test framework.
 *
 * @group graphql
 */
class TestFrameworkTest extends GraphQLTestBase {

  /**
   * Test mocked fields.
   */
  public function testFieldMock() {
    $schema = <<<GQL
      type Query {
        root: String
      }
GQL;

    $this->setUpSchema($schema);

    $cacheable = $this->getMockBuilder(CacheableDependencyInterface::class)
      ->setMethods(['getCacheTags', 'getCacheMaxAge', 'getCacheContexts'])
      ->getMock();
    $cacheable->expects($this->any())
      ->method('getCacheTags')
      ->willReturn(['my_tag']);
    $cacheable->expects($this->any())
      ->method('getCacheMaxAge')
      ->willReturn(42);
    $cacheable->expects($this->any())
      ->method('getCacheContexts')
      ->willReturn([]);

    $this->mockField('root', [
      'name' => 'root',
      'type' => 'String',
      'parent' => 'Query',
      'response_cache_tags' => ['my_tag'],
    ], $this->builder->compose(
        $this->builder->fromValue($cacheable),
        $this->builder->fromValue('test')
      )
    );

    $metadata = $this->defaultCacheMetaData();
    $metadata->setCacheMaxAge(42);
    $metadata->addCacheTags([
      'my_tag',
    ]);

    $schema = $this->introspect();
    $this->assertArraySubset([
      'types' => [
        'Query' => [
          'fields' => [
            'root' => [
              'name' => 'root',
              'type' => [
                'kind' => 'SCALAR',
                'name' => 'String',
              ],
            ],
          ],
        ],
      ],
    ], $schema);

    $this->assertResults('{ root }', [], [
      'root' => 'test',
    ], $metadata);
  }

  /**
   * Test result error assertions.
   */
  public function testErrorAssertion() {
    $schema = <<<GQL
      type Query {
        wrongname: String
      }
GQL;

    $this->setUpSchema($schema);
    // Errors are cacheable now.
    $metadata = new CacheableMetadata();
    $metadata->setCacheMaxAge(0);
    $metadata->setCacheContexts($this->defaultCacheContexts());

    $this->assertErrors('{ root }', [], [
      'Cannot query field "root" on type "Query".',
    ], $metadata);

    $this->assertErrors('{ root }', [], [
      '/root.*Query/',
    ], $metadata);
  }

  /**
   * Test mutation mocking.
   */
  public function testMutationMock() {
    $schema = <<<GQL
      schema {
        query: Query
        mutation: Mutation
      }
      type Query {
        root: Boolean
      }

      type Mutation {
        addUser(user: User!): Boolean
      }

      enum Gender {
        Female
        Male
      }

      input User {
        name: String
        age: Int
        gender: Gender
      }
GQL;

    $this->setUpSchema($schema);

    $this->mockField('root', [
      'name' => 'root',
      'type' => 'Boolean',
      'parent' => 'Query',
    ], $this->builder->fromValue(TRUE));

    $this->mockField('addUser', [
      'name' => 'addUser',
      'type' => 'Boolean',
      'parent' => 'Mutation',
    ], $this->builder->compose(
        $this->builder->fromArgument('user'),
        $this->builder->callback(function ($value, $args, ResolveContext $context, ResolveInfo $info) {
          return $args['user']['age'] > 50 && $args['user']['gender'] == 'Male';
        })
      )
    );

    $metadata = $this->defaultMutationCacheMetaData();
    $this->assertResults('mutation ($user: User!) { addUser(user: $user) }', [
      'user' => [
        'name' => 'John Doe',
        'age' => 52,
        'gender' => 'Male',
      ],
    ], ['addUser' => TRUE], $metadata);
  }

  /**
   * Test interface mocking.
   */
  public function testInterfaceMock() {
    $schema = <<<GQL
      schema {
        query: Query
      }
      type Query {
        root: [Token]
      }

      interface Token {
        id: Int
      }

      type Number implements Token {
        value: Int
      }

      type Word implements Token {
        value: String
      }
GQL;

    $this->setUpSchema($schema);
    $this->mockTypeResolver('Token', function ($value, $context, $info) {
      return is_int($value['value']) ? 'Number' : 'Word';
    });

    $this->mockField('value', [
      'name' => 'value',
      'type' => 'Int',
      'parent' => 'Number',
    ], $this->builder->compose(
        $this->builder->fromParent(),
        $this->builder->callback(function ($value, $args, ResolveContext $context, ResolveInfo $info) {
          return $value['value'];
        })
      )
    );

    $this->mockField('value', [
      'name' => 'value',
      'type' => 'String',
      'parent' => 'Word',
    ], $this->builder->compose(
        $this->builder->fromParent(),
        $this->builder->callback(function ($value, $args, ResolveContext $context, ResolveInfo $info) {
          return $value['value'];
        })
      )
    );

    $this->mockField('root', [
      'name' => 'root',
      'type' => '[Token]',
      'parent' => 'Query',
    ], $this->builder->fromValue(
      [
        ['value' => 42],
        ['value' => 'GraphQL'],
      ]
    ));

    $this->assertResults('{ root { ... on Number { number:value } ... on Word { word:value }  } }', [], [
      'root' => [
        0 => ['number' => 42],
        1 => ['word' => 'GraphQL'],
      ],
    ], $this->defaultCacheMetaData());
  }

  /**
   * Test union mocks.
   * @todo Unions are identical to interfaces right now, but they should not be.
   */
  public function testUnionMock() {
    $schema = <<<GQL
      schema {
        query: Query
      }
      type Query {
        root: [Token]
      }

      union Token = Number | Word

      type Number implements Token {
        value: Int
      }

      type Word implements Token {
        value: String
      }
GQL;
    $this->setUpSchema($schema);
    $this->mockTypeResolver('Token', function ($value, $context, $info) {
      return is_int($value['value']) ? 'Number' : 'Word';
    });

    $this->mockField('value', [
      'name' => 'value',
      'type' => 'Int',
      'parent' => 'Number',
    ], $this->builder->compose(
        $this->builder->fromParent(),
        $this->builder->callback(function ($value, $args, ResolveContext $context, ResolveInfo $info) {
          return $value['value'];
        })
      )
    );

    $this->mockField('value', [
      'name' => 'value',
      'type' => 'String',
      'parent' => 'Word',
    ], $this->builder->compose(
        $this->builder->fromParent(),
        $this->builder->callback(function ($value, $args, ResolveContext $context, ResolveInfo $info) {
          return $value['value'];
        })
      )
    );

    $this->mockField('root', [
      'name' => 'root',
      'type' => '[Token]',
      'parent' => 'Query',
    ], $this->builder->fromValue(
      [
        ['value' => 42],
        ['value' => 'GraphQL'],
      ]
    ));

    $this->assertResults('{ root { ... on Number { number:value } ... on Word { word:value }  } }', [], [
      'root' => [
        0 => ['number' => 42],
        1 => ['word' => 'GraphQL'],
      ],
    ], $this->defaultCacheMetaData());
  }

}
