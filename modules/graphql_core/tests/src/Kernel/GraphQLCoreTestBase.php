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
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // User entity schema is required for the currentUserContext field.
    $this->installEntitySchema('user');
  }

  /**
   * {@inheritdoc}
   */
  protected function defaultCacheTags() {
    // graphql_core derives fields and types from entity information, so the
    // cache tags are applied to the schema and end up in every result.
    //
    // https://github.com/drupal-graphql/graphql/issues/500
    return array_merge(parent::defaultCacheTags(), [
      'entity_bundles',
      'entity_field_info',
      'entity_types',
    ]);
  }

}
