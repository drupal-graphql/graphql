<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql\Traits\ByPassAccessTrait;
use Drupal\Tests\graphql\Traits\MockTypeSystemTrait;
use Drupal\Tests\graphql\Traits\QueryTrait;
use Drupal\Tests\graphql\Traits\MockSchemaTrait;
use Youshido\GraphQL\Execution\ResolveInfo;

class MockingFrameworkTest extends KernelTestBase {

  use QueryTrait;
  use ByPassAccessTrait;
  use MockSchemaTrait;
  use MockTypeSystemTrait;

  public static $modules = [
    'system',
    'graphql',
  ];

  function getSchemaDefinitions() {
    return [
      'default' => [
        'id' => 'default',
        'name' => 'default',
        'path' => 'graphql',
      ],
    ];
  }

  public function register(ContainerBuilder $container) {
    parent::register($container);
    $this->registerSchemaPluginManager($container);
    $this->registerTypeSystemPluginManagers($container);
  }

  protected function setUp() {
    parent::setUp();
    $this->byPassAccess();
    $this->installConfig('system');
    $this->installConfig('graphql');
  }

  public function testFieldMock() {
    $this->mockField('root', [
      'name' => 'root',
      'type' => 'String',
    ], function ($value, array $args, ResolveInfo $info) {
      yield 'test';
    });

    $result = $this->query('{ root }');
    $this->assertEquals([
      'data' => [
        'root' => 'test',
      ],
    ], json_decode($result->getContent(), TRUE));
  }

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

    $result = json_decode($this->query('{ root { value } }')->getContent(), TRUE);
    $this->assertEquals([
      'data' => [
        'root' => ['value' => 'test'],
      ],
    ], $result);
  }

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

    $result = json_decode($this->query('mutation ($user: User!) { addUser(user: $user) }', [
      'user' => [
        'name' => 'John Doe',
        'age' => 52,
        'gender' => 'Male',
      ],
    ])->getContent(), TRUE);

    $this->assertEquals([
      'data' => [
        'addUser' => TRUE,
      ],
    ], $result);
  }

  public function testInterfaceMock() {

    $this->mockInterface('token', [
      'name' => 'Token',
    ]);

    $this->mockType('number', [
      'name' => 'Number',
      'interfaces' => ['Token'],
    ], function ($value) {
      return is_integer($value['value']);
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

    $result = json_decode($this->query('{ root { ... on Number { number:value } ... on Word { word:value }  } }')->getContent(), TRUE);

    $this->assertEquals([
      'data' => [
        'root' => [
          0 => ['number' => 42],
          1 => ['word' => 'GraphQL'],
        ],
      ],
    ], $result);
  }

  /**
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

    $result = json_decode($this->query('{ root { ... on Number { number:value } ... on Word { word:value }  } }')->getContent(), TRUE);

    $this->assertEquals([
      'data' => [
        'root' => [
          0 => ['number' => 42],
          1 => ['word' => 'GraphQL'],
        ],
      ],
    ], $result);
  }

}