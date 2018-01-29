<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\graphql\Plugin\GraphQL\Scalars\GraphQLString;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql\Traits\ByPassAccessTrait;
use Drupal\Tests\graphql\Traits\QueryTrait;
use Drupal\Tests\graphql\Traits\SchemaProphecyTrait;

class SchemaProphecyTest extends KernelTestBase {
  use QueryTrait;
  use ByPassAccessTrait;
  use SchemaProphecyTrait;

  public static $modules = [
    'system',
    'path',
    'user',
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
    $this->installConfig('user');
    $this->installEntitySchema('user');
  }

  public function testSchemaProphecy() {
    $field = $this->getMockBuilder(FieldPluginBase::class)
      ->setConstructorArgs([
        [], 'root', [
          'id' => 'root',
          'name' => 'root',
          'type' => 'String',
          'parents' => [],
          'weight' => 0,
          'nullable' => TRUE,
          'multi' => FALSE,
          'arguments' => [],
          'pluginType' => GRAPHQL_FIELD_PLUGIN,
        ],
      ])
      ->setMethods([
        'resolveValues',
      ])->getMock();

    $field
      ->expects(static::any())
      ->method('resolveValues')
      ->willreturn(new \ArrayIterator(['test']));

    $this->addPlugin(new GraphQLString([], 'graphql_string', [
      'id' => 'string',
      'name' => 'String',
      'data_type' => 'string',
      'weight' => 0,
      'pluginType' => GRAPHQL_SCALAR_PLUGIN,
    ]));
    $this->addPlugin($field);

    $result = $this->query('{ root }');
    $this->assertEquals([
      'data' => [
        'root' => 'test',
      ],
    ], json_decode($result->getContent(), TRUE));
  }

}