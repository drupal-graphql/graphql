<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\graphql\Annotation\GraphQLField;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\graphql\Plugin\GraphQL\Scalars\GraphQLString;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql\Traits\ByPassAccessTrait;
use Drupal\Tests\graphql\Traits\MockTypeSystemTrait;
use Drupal\Tests\graphql\Traits\QueryTrait;
use Drupal\Tests\graphql\Traits\MockSchemaTrait;
use Youshido\GraphQL\Execution\ResolveInfo;

class SchemaProphecyTest extends KernelTestBase {
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

  public function testSchemaProphecy() {
    $field = $this->mockField('root', [
      'name' => 'root',
      'type' => 'String',
    ], function ($value, array $args, ResolveInfo $info) {
      yield 'test';
    });
    $this->addTypeSystemPlugin($field);

    $result = $this->query('{ root }');
    $this->assertEquals([
      'data' => [
        'root' => 'test',
      ],
    ], json_decode($result->getContent(), TRUE));
  }

}