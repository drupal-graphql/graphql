<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\SchemaBuilder;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\graphql\GraphQL\ResolverBuilder;
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
    $gql_schema = <<<GQL
      type Query {
        root: String
      }
GQL;
    $this->setUpSchema($gql_schema, $this->getDefaultSchema());

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


    $builder = new ResolverBuilder();
    $this->mockField('root', [
      'name' => 'root',
      'type' => 'String',
      'parent' => 'Query',
      'response_cache_tags' => ['my_tag'],
    ], $builder->compose(
        $builder->fromValue($cacheable),
        $builder->fromValue('test')
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
    $gql_schema = <<<GQL
    type Query {
      wrongname: String
    }
GQL;
    $this->setUpSchema($gql_schema, $this->getDefaultSchema());
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
    $gql_schema = <<<GQL
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
    $this->setUpSchema($gql_schema, $this->getDefaultSchema());
    $builder = new ResolverBuilder();

    $this->mockField('root', [
      'name' => 'root',
      'type' => 'Boolean',
      'parent' => 'Query',
    ], $builder->fromValue(TRUE));

    $this->mockField('addUser', [
      'name' => 'addUser',
      'type' => 'Boolean',
      'parent' => 'Mutation',
    ], $builder->compose(
        $builder->fromArgument('user'),
        function ($value, $args, ResolveContext $context, ResolveInfo $info) {
          return $args['user']['age'] > 50 && $args['user']['gender'] == 'Male';
        }
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
    $gql_schema = <<<GQL
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
    $this->setUpSchema($gql_schema, $this->getDefaultSchema());
    $builder = new ResolverBuilder();

    $this->mockTypeResolver('Token', function ($value, $context, $info) {
      return is_int($value['value']) ? 'Number' : 'Word';
    });

    $this->mockField('value', [
      'name' => 'value',
      'type' => 'Int',
      'parent' => 'Number',
    ], $builder->compose(
        $builder->fromParent(),
        function ($value, $args, ResolveContext $context, ResolveInfo $info) {
          return $value['value'];
        }
      )
    );

    $this->mockField('value', [
      'name' => 'value',
      'type' => 'String',
      'parent' => 'Word',
    ], $builder->compose(
        $builder->fromParent(),
        function ($value, $args, ResolveContext $context, ResolveInfo $info) {
          return $value['value'];
        }
      )
    );

    $this->mockField('root', [
      'name' => 'root',
      'type' => '[Token]',
      'parent' => 'Query',
    ], $builder->fromValue(
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
    $gql_schema = <<<GQL
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
    $this->setUpSchema($gql_schema, $this->getDefaultSchema());
    $builder = new ResolverBuilder();

    $this->mockTypeResolver('Token', function ($value, $context, $info) {
      return is_int($value['value']) ? 'Number' : 'Word';
    });

    $this->mockField('value', [
      'name' => 'value',
      'type' => 'Int',
      'parent' => 'Number',
    ], $builder->compose(
        $builder->fromParent(),
        function ($value, $args, ResolveContext $context, ResolveInfo $info) {
          return $value['value'];
        }
      )
    );

    $this->mockField('value', [
      'name' => 'value',
      'type' => 'String',
      'parent' => 'Word',
    ], $builder->compose(
        $builder->fromParent(),
        function ($value, $args, ResolveContext $context, ResolveInfo $info) {
          return $value['value'];
        }
      )
    );

    $this->mockField('root', [
      'name' => 'root',
      'type' => '[Token]',
      'parent' => 'Query',
    ], $builder->fromValue(
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
