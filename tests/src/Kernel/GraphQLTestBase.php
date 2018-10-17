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
use PHPUnit_Framework_Error_Notice;
use PHPUnit_Framework_Error_Warning;

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
    return 'graphql_test';
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
      'graphql_response',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function defaultCacheContexts() {
    return ['user.permissions'];
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
    // TODO: rewrite injection.
    //$this->injectTypeSystemPluginManagers($this->container);

    PHPUnit_Framework_Error_Warning::$enabled = FALSE;

    $this->injectAccount();
    $this->installConfig('system');
    $this->installConfig('graphql');
    //$this->mockSchema('default');
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('node', ['node_access']);

    $this->installEntitySchema('graphql_server');

    $this->installEntitySchema('configurable_language');
    $this->installConfig(['language']);
    $this->container->get('language_negotiator')
      ->setCurrentUser($this->accountProphecy->reveal());

    ConfigurableLanguage::create([
      'id' => 'fr',
      'weight' => 1,
    ])->save();

    ConfigurableLanguage::create([
      'id' => 'de',
      'weight' => 2,
    ])->save();
  }

}
