<?php

namespace Drupal\Tests\graphql\Traits;

/**
 * Trait for retrieving GraphQL queries from files.
 */
trait QueryFileTrait {

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
  public function getQueryFromFile($queryFile) {
    return file_get_contents($this->getQueriesDirectory() . '/' . $queryFile);
  }

}
