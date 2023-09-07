<?php

namespace Drupal\Tests\graphql_core\Kernel;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test base for drupal core graphql functionality.
 */
class GraphQLCoreTestBase extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql_core',
    'path_alias',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // User entity schema is required for the currentUserContext field.
    $this->container->get('module_handler')->loadInclude('user', 'install');
    $this->installEntitySchema('user');
    user_install();
  }

}
