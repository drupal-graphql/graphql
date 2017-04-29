<?php

namespace Drupal\Tests\graphql_core;

use Drupal\graphql\GraphQL\Execution\Processor;
use Drupal\Tests\token\Kernel\KernelTestBase;
use Drupal\user\Entity\Role;

/**
 * Run tests against a *.gql query file.
 */
abstract class GraphQLFileTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql',
    'graphql_core',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('graphql');
    $this->installConfig('user');
    $this->installEntitySchema('user');

    Role::load('anonymous')
      ->grantPermission('execute graphql requests')
      ->save();
  }

  /**
   * Get the path to the directory containing test query files.
   *
   * @return string
   *   The path to the collection of test query files.
   */
  protected function getQueriesDirectory() {
    return drupal_get_path('module', explode('\\', get_class($this))[2]) . '/tests/queries';
  }

  /**
   * Submit a GraphQL query.
   *
   * @param string $query_file
   *   The query file name.
   * @param mixed $variables
   *   Variables to be passed to the query file.
   *
   * @return array
   *   The GraphQL result object.
   */
  public function executeQueryFile($query_file, $variables = [], $assertNoErrors = TRUE) {
    $processor = new Processor($this->container, $this->container->get('graphql.schema'));
    $file = $this->getQueriesDirectory() . '/' . $query_file;
    $result = $processor->processPayload(file_get_contents($file), $variables);
    $data = $result->getResponseData();
    if ($assertNoErrors) {
      $errors = array_map(function ($error) {
        return $error['message'];
      }, array_key_exists('errors', $data) ? $data['errors'] : []);
      $this->assertEquals([], $errors);
    }
    return $data;
  }

}
