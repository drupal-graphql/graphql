<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Youshido\GraphQL\Execution\ResolveInfo;

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
    $this->mockField('root', [
      'name' => 'root',
      'type' => 'String',
      'response_cache_tags' => ['my_tag'],
    ], function () {
      yield (new CacheableValue('test'))->mergeCacheMaxAge(42);
    });

    $metadata = $this->defaultCacheMetaData();
    $metadata->setCacheMaxAge(42);
    $metadata->addCacheTags([
      'my_tag',
    ]);

    $schema = $this->introspect();
    $this->assertArraySubset([
      'types' => [
        'RootQuery' => [
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
    $metadata = $this->defaultCacheMetaData();

    // Errors are not cached at all.
    $metadata->setCacheMaxAge(0);
    $metadata->setCacheContexts(['gql']);

    $this->assertErrors('{ root }', [], [
      'Schema has to have fields',
      'Field "root" not found in type "RootQuery". Available fields are: "__schema", "__type"',
    ], $metadata);
  }

  /**
   * Test type mocking.
   */
  public function testTypeMock() {
    $this->mockField('value', [
      'name' => 'value',
      'parents' => ['Test'],
      'type' => 'String',
    ], function ($value, array $args, ResolveInfo $info) {
      yield $value['value'];
    });

    $this->mockType('test', [
      'name' => 'Test',
    ]);

    $this->mockField('root', [
      'name' => 'root',
      'type' => 'Test',
    ], function ($value, array $args, ResolveInfo $info) {
      yield ['value' => 'test'];
    });

    $this->assertResults('{ root { value } }', [], [
      'root' => ['value' => 'test'],
    ], $this->defaultCacheMetaData());
  }

  /**
   * Test mutation mocking.
   */
  public function testMutationMock() {
    // Fake at least a root field, or the schema will return an error.
    $this->mockField('root', [
      'name' => 'root',
      'type' => 'Boolean',
    ], TRUE);

    $this->mockEnum('gender', [
      'name' => 'Gender',
    ], function (PluggableSchemaBuilderInterface $builder) {
      return [
        ['value' => 'f', 'name' => 'Female', 'description' => ''],
        ['value' => 'm', 'name' => 'Male', 'description' => ''],
      ];
    });

    $this->mockInputType('user', [
      'name' => 'User',
      'fields' => [
        'name' => 'String',
        'age' => 'Int',
        'gender' => 'Gender',
      ],
    ]);

    $this->mockMutation('addUser', [
      'name' => 'addUser',
      'type' => 'Boolean',
      'arguments' => [
        'user' => 'User',
      ],
    ], function ($value, $args) {
      return $args['user']['age'] > 50 && $args['user']['gender'] == 'm';
    });

    $metadata = $this->defaultCacheMetaData();
    // Mutations are never cacheable.
    $metadata->setCacheMaxAge(0);

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
    $this->mockInterface('token', [
      'name' => 'Token',
    ]);

    $this->mockType('number', [
      'name' => 'Number',
      'interfaces' => ['Token'],
    ], function ($value) {
      return is_int($value['value']);
    });

    $this->mockType('word', [
      'name' => 'Word',
      'interfaces' => ['Token'],
    ], function ($value) {
      return is_string($value['value']);
    });

    $this->mockField('int_value', [
      'name' => 'value',
      'type' => 'Int',
      'parents' => ['Number'],
    ], function ($value) {
      yield $value['value'];
    });

    $this->mockField('string_value', [
      'name' => 'value',
      'type' => 'String',
      'parents' => ['Word'],
    ], function ($value) {
      yield $value['value'];
    });

    $this->mockField('root', [
      'name' => 'root',
      'type' => '[Token]',
    ], function () {
      yield ['value' => 42];
      yield ['value' => 'GraphQL'];
    });

    $this->assertResults('{ root { ... on Number { number:value } ... on Word { word:value }  } }', [], [
      'root' => [
        0 => ['number' => 42],
        1 => ['word' => 'GraphQL'],
      ],
    ], $this->defaultCacheMetaData());
  }

  /**
   * Test union mocks.
   *
   * @todo Unions are identical to interfaces right now, but they should not be.
   */
  public function testUnionMock() {
    $this->mockUnion('token', [
      'name' => 'Token',
    ]);

    $this->mockType('number', [
      'name' => 'Number',
      'unions' => ['Token'],
    ], function ($value) {
      return is_integer($value['value']);
    });

    $this->mockType('word', [
      'name' => 'Word',
      'unions' => ['Token'],
    ], function ($value) {
      return is_string($value['value']);
    });

    $this->mockField('int_value', [
      'name' => 'value',
      'type' => 'Int',
      'parents' => ['Number'],
    ], function ($value) { yield $value['value']; });

    $this->mockField('string_value', [
      'name' => 'value',
      'type' => 'String',
      'parents' => ['Word'],
    ], function ($value) { yield $value['value']; });

    $this->mockField('root', [
      'name' => 'root',
      'type' => '[Token]',
    ], function () {
      yield ['value' => 42];
      yield ['value' => 'GraphQL'];
    });

    $this->assertResults('{ root { ... on Number { number:value } ... on Word { word:value }  } }', [], [
      'root' => [
        0 => ['number' => 42],
        1 => ['word' => 'GraphQL'],
      ],
    ], $this->defaultCacheMetaData());
  }

}