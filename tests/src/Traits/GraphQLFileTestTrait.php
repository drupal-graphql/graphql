<?php

namespace Drupal\Tests\graphql\Traits;

use Symfony\Component\HttpFoundation\Request;

/**
 * Trait for running tests against GraphQL query files.
 *
 * @deprecated
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
   * Retrieve the GraphQL query stored in a file as string.
   *
   * @param string $queryFile
   *   The query file name.
   *
   * @return string
   *   The graphql query string.
   */
  public function getQuery($queryFile) {
    return file_get_contents($this->getQueriesDirectory() . '/' . $queryFile);
  }

  /**
   * Assert that the result contains no errors.
   *
   * @param array $data
   *   The query result.
   */
  public function assertNoErrors(array $data) {
    $errors = array_map(function ($error) {
      return $error['message'];
    }, array_key_exists('errors', $data) ? $data['errors'] : []);
    $this->assertEquals([], $errors, 'Invalid GraphQL query. Errors: ' . implode("\n* ", $errors));
  }

  /**
   * Run http subrequest with a specific query file.
   *
   * @param string $queryFile
   *   The query file name.
   * @param mixed $variables
   *   Variables to be passed to the query file.
   * @param bool $assertNoErrors
   *   Assert the absence of errors.
   *
   * @return array
   *   The GraphQL result object.
   *
   * @throws \Exception
   */
  public function requestWithQueryFile($queryFile, $variables = [], $assertNoErrors = TRUE) {
    $content = [
      'query' => $this->getQuery($queryFile),
      'variables' => $variables,
    ];

    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel */
    $httpKernel = \Drupal::service('http_kernel');
    $response = $httpKernel->handle(Request::create('/graphql', 'POST', [], [], [], [], json_encode($content)));
    $data = json_decode($response->getContent(), TRUE);

    if ($assertNoErrors) {
      $this->assertNoErrors($data);
    }

    return $data;
  }

}
