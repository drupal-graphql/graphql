<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\Cache\Cache;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\graphql\Traits\DataProducerExecutionTrait;
use Drupal\Tests\graphql\Traits\MockingTrait;
use Drupal\Tests\graphql\Traits\HttpRequestTrait;
use Drupal\Tests\graphql\Traits\QueryFileTrait;
use Drupal\Tests\graphql\Traits\QueryResultAssertionTrait;
use Drupal\Tests\graphql\Traits\SchemaPrinterTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;

abstract class GraphQLTestBase extends KernelTestBase {
  use DataProducerExecutionTrait;
  use HttpRequestTrait;
  use QueryFileTrait;
  use QueryResultAssertionTrait;
  use SchemaPrinterTrait;
  use MockingTrait;
  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'user',
    'language',
    'node',
    'graphql',
    'content_translation',
    'entity_reference_test',
    'field',
    'menu_link_content',
    'link',
    'typed_data',
  ];

  /**
   * @var \Drupal\graphql\GraphQL\ResolverBuilder
   */
  protected $builder;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig('system');
    $this->installConfig('graphql');
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('node', ['node_access']);
    $this->installSchema('user', ['users_data']);
    $this->installEntitySchema('graphql_server');
    $this->installEntitySchema('configurable_language');
    $this->installConfig(['language']);

    $this->setUpCurrentUser([], $this->userPermissions());

    ConfigurableLanguage::create([
      'id' => 'fr',
      'weight' => 1,
    ])->save();

    ConfigurableLanguage::create([
      'id' => 'de',
      'weight' => 2,
    ])->save();

    $this->builder = new ResolverBuilder();
  }

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinitions() {
    return [
      'default' => [
        'id' => 'default',
        'name' => 'default',
        'path' => 'graphql',
        'deriver' => 'Drupal\graphql\Plugin\Deriver\PluggableSchemaDeriver',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function defaultCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  protected function defaultCacheTags() {
    $tags = ['graphql_response'];
    if (isset($this->server)) {
      array_push($tags, "config:graphql.graphql_servers.{$this->server->id()}");
    }

    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  protected function defaultCacheContexts() {
    return ['user.permissions'];
  }

  /**
   * @return array
   */
  protected function userPermissions() {
    return ['access content', 'bypass graphql access'];
  }

}
