<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\Cache\Cache;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\graphql\Traits\ProphesizePermissionsTrait;
use Drupal\Tests\graphql\Traits\EnableCliCacheTrait;
use Drupal\Tests\graphql\Traits\HttpRequestTrait;
use Drupal\Tests\graphql\Traits\IntrospectionTestTrait;
use Drupal\Tests\graphql\Traits\MockSchemaTrait;
use Drupal\Tests\graphql\Traits\MockGraphQLPluginTrait;
use Drupal\Tests\graphql\Traits\QueryFileTrait;
use Drupal\Tests\graphql\Traits\QueryResultAssertionTrait;

/**
 * Base class for GraphQL tests.
 */
abstract class GraphQLTestBase extends KernelTestBase {
  use EnableCliCacheTrait;
  use ProphesizePermissionsTrait;
  use MockGraphQLPluginTrait;
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
    'language',
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
        'deriver' => 'Drupal\graphql\Plugin\Deriver\PluggableSchemaDeriver',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultSchema() {
    return 'default:default';
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
    return [
      'graphql',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function defaultCacheContexts() {
    return [
      'user.permissions',
      'languages:language_url',
      'languages:language_interface',
      'languages:language_content',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->injectTypeSystemPluginManagers($this->container);

    $this->injectAccount();
    $this->installConfig('system');
    $this->installConfig('graphql');
    $this->mockSchema('default');

    $this->installEntitySchema('configurable_language');
    $this->installConfig(['language']);
    $this->container->get('language_negotiator')
      ->setCurrentUser($this->accountProphecy->reveal());

    ConfigurableLanguage::create([
      'id' => 'fr',
      'weight' => 1,
    ])->save();
  }

}
