<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql\Traits\GraphQLFileTestTrait;
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
    'graphql_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('system');
    $this->installConfig('graphql');
    $this->installConfig('user');
    $this->installEntitySchema('user');

    Role::load('anonymous')
      ->grantPermission('execute graphql requests')
      ->save();
  }

}
