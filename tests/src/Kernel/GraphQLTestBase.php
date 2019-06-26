<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\Cache\Cache;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\graphql\Traits\DataProducerExecutionTrait;
use Drupal\Tests\graphql\Traits\MockingTrait;
use Drupal\Tests\graphql\Traits\ProphesizePermissionsTrait;
use Drupal\Tests\graphql\Traits\HttpRequestTrait;
use Drupal\Tests\graphql\Traits\QueryFileTrait;
use Drupal\Tests\graphql\Traits\QueryResultAssertionTrait;
use Drupal\Tests\graphql\Traits\SchemaPrinterTrait;

abstract class GraphQLTestBase extends KernelTestBase {
  use ProphesizePermissionsTrait;
  use DataProducerExecutionTrait;
  use HttpRequestTrait;
  use QueryFileTrait;
  use QueryResultAssertionTrait;
  use SchemaPrinterTrait;
  use MockingTrait;

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

    \PHPUnit_Framework_Error_Warning::$enabled = FALSE;

    $this->injectAccount();
    $this->installConfig('system');
    $this->installConfig('graphql');
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
  protected function getDefaultSchema() {
    return 'test';
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

}
