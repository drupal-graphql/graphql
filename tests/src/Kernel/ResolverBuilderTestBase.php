<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel;

/**
 * Shared schema for resolver builder tests.
 */
abstract class ResolverBuilderTestBase extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'graphql_dataproducers_test',
    'graphql_resolver_builder_test',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $schema = <<<GQL
      type Query {
        me: String
        tree(id: Int): Tree
      }

      type Tree {
        id(someArg: Int): Int
        name: String
        uri: String
        context: Context
      }

      type Context {
        myContext: String
      }
GQL;

    $this->setUpSchema($schema);
  }

}
