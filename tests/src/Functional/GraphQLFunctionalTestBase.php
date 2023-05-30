<?php

namespace Drupal\Tests\graphql\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\graphql\Traits\GraphQLFunctionalTestsTrait;
use Drupal\Tests\graphql\Traits\QueryFileTrait;

/**
 * The base class for all functional GraphQL tests.
 */
abstract class GraphQLFunctionalTestBase extends BrowserTestBase {

  use GraphQLFunctionalTestsTrait;
  use QueryFileTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'graphql',
    'typed_data',
    'graphql_examples',
    'node',
    'text',
    'field',
    'filter',
  ];

  /**
   * This can be removed, once the linked issue is resolved.
   *
   * @todo See https://github.com/drupal-graphql/graphql/issues/1177.
   *
   * {@inheritdoc}
   */
  protected static $configSchemaCheckerExclusions = [
    'graphql.graphql_servers.testing',
  ];

}
