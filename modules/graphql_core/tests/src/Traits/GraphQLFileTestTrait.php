<?php

namespace Drupal\Tests\graphql_core\Traits;

use Drupal\graphql\GraphQL\Execution\Processor;

/**
 * Trait for running tests against GraphQL query files.
 */
trait GraphQLFileTestTrait {

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
   * @param string $queryFile
   *   The query file name.
   * @param mixed $variables
   *   Variables to be passed to the query file.
   *
   * @return array
   *   The GraphQL result object.
   */
  public function executeQueryFile($queryFile, $variables = [], $assertNoErrors = TRUE) {
    $processor = new Processor($this->container, $this->container->get('graphql.schema'));
    $file = $this->getQueriesDirectory() . '/' . $queryFile;
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
