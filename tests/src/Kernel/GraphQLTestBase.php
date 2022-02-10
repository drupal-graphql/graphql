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

/**
 * Provides helper methods for kernel tests in GraphQL module.
 */
abstract class GraphQLTestBase extends KernelTestBase {
  use DataProducerExecutionTrait;
  use HttpRequestTrait;
  use QueryFileTrait;
  use QueryResultAssertionTrait;
  use SchemaPrinterTrait;
  use MockingTrait;
  use UserCreationTrait;

  /**
   * The server under test.
   *
   * @var \Drupal\graphql\Entity\Server|null
   */
  protected $server;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
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
  protected function setUp(): void {
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
    $this->installEntitySchema('menu_link_content');

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
   * Returns the default cache maximum age for the test.
   */
  protected function defaultCacheMaxAge(): int {
    return Cache::PERMANENT;
  }

  /**
   * Returns the default cache tags used in assertions for this test.
   *
   * @return string[]
   *   The list of cache tags.
   */
  protected function defaultCacheTags(): array {
    $tags = ['graphql_response'];
    if (isset($this->server)) {
      array_push($tags, "config:graphql.graphql_servers.{$this->server->id()}");
    }

    return $tags;
  }

  /**
   * Returns the default cache contexts used in assertions for this test.
   *
   * @return string[]
   *   The list of cache contexts.
   */
  protected function defaultCacheContexts(): array {
    return ['user.permissions'];
  }

  /**
   * Provides the user permissions that the test user is set up with.
   *
   * @return string[]
   *   List of user permissions.
   */
  protected function userPermissions(): array {
    return ['access content', 'bypass graphql access'];
  }

}
