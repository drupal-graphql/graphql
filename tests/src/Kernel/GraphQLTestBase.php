<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\Cache\Cache;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql\Traits\ProphesizePermissionsTrait;
use Drupal\Tests\graphql\Traits\EnableCliCacheTrait;
use Drupal\Tests\graphql\Traits\HttpRequestTrait;
use Drupal\Tests\graphql\Traits\IntrospectionTestTrait;
use Drupal\Tests\graphql\Traits\MockSchemaTrait;
use Drupal\Tests\graphql\Traits\MockTypeSystemTrait;
use Drupal\Tests\graphql\Traits\QueryFileTrait;
use Drupal\Tests\graphql\Traits\QueryResultAssertionTrait;

/**
 * Base class for GraphQL tests.
 */
abstract class GraphQLTestBase extends KernelTestBase {
  use EnableCliCacheTrait;
  use ProphesizePermissionsTrait;
  use MockSchemaTrait;
  use MockTypeSystemTrait;
  use HttpRequestTrait;
  use QueryResultAssertionTrait;
  use IntrospectionTestTrait;
  use QueryFileTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'user',
    'graphql',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinitions() {
    return [
      'default' => [
        'id' => 'default',
        'name' => 'default',
        'path' => 'graphql',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultSchema() {
    return 'default';
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
    return ['graphql_response', 'graphql_schema'];
  }

  /**
   * {@inheritdoc}
   */
  protected function defaultCacheContexts() {
    return ['gql', 'languages:language_interface', 'user.permissions'];
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);
    $this->registerSchemaPluginManager($container);
    $this->registerTypeSystemPluginManagers($container);
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->injectAccount();
    $this->installConfig('system');
    $this->installConfig('graphql');
  }

}
