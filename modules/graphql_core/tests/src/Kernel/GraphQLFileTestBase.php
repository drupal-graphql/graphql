<?php

namespace Drupal\Tests\graphql_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql_core\Traits\GraphQLFileTestTrait;
use Drupal\graphql_content\ContentEntitySchemaConfig;
use Drupal\user\Entity\Role;

/**
 * Run tests against a *.gql query file.
 */
abstract class GraphQLFileTestBase extends KernelTestBase {
  use GraphQLFileTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'path',
    'user',
    'graphql',
    'graphql_core',
  ];

  /**
   * The schema configuration service.
   *
   * @var \Drupal\graphql_content\ContentEntitySchemaConfig
   */
  protected $schemaConfig;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('system');
    $this->installConfig('graphql');
    $this->installConfig('user');
    $this->installEntitySchema('user');

    // TODO: is this the right way to do it?
    $this->schemaConfig = new ContentEntitySchemaConfig(\Drupal::configFactory());

    Role::load('anonymous')
      ->grantPermission('execute graphql requests')
      ->save();
  }

}
