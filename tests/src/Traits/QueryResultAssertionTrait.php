<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\Execution\QueryResult;
use GraphQL\Error\Error;
use GraphQL\Server\OperationParams;

/**
 * Trait for easier assertion on GraphQL query results.
 */
trait QueryResultAssertionTrait {

  /**
   * Return the default schema for this test.
   *
   * @return string
   *   The default schema id.
   */
  abstract protected function getDefaultSchema();

  /**
   * Return the default cache max age for this test case.
   *
   * @return int
   *   The default max age value.
   */
  abstract protected function defaultCacheMaxAge();

  /**
   * Return the default cache cache tags for this test case.
   *
   * @return string[]
   *   The default cache tags.
   */
  abstract protected function defaultCacheTags();

  /**
   * Return the default cache contexts for this test case.
   *
   * @return string[]
   *   The default cache contexts.
   */
  abstract protected function defaultCacheContexts();


  /**
   * Retrieve the graphql processor.
   *
   * @return \Drupal\graphql\GraphQL\Execution\QueryProcessor
   *   The graphql processor service.
   */
  protected function graphQlProcessor() {
    return $this->container->get('graphql.query_processor');
  }

  /**
   * The default cache metadata object.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cache metadata object.
   */
  protected function defaultCacheMetaData() {
    $metadata = new CacheableMetadata();
    $metadata->setCacheMaxAge($this->defaultCacheMaxAge());
    $metadata->setCacheTags($this->defaultCacheTags());
    $metadata->setCacheContexts($this->defaultCacheContexts());
    return $metadata;
  }

  /**
   * The default mutation cache metadata object.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cache metadata object.
   */
  protected function defaultMutationCacheMetaData() {
    $metadata = new CacheableMetadata();
    $metadata->setCacheContexts($this->defaultCacheContexts());
    $metadata->setCacheMaxAge(0);
    $metadata->setCacheTags($this->defaultCacheTags());
    return $metadata;
  }

  /**
   * Assert a result for a graphql query and variables.
   *
   * @param string $query
   *   The query string.
   * @param array $variables
   *   The query variables.
   * @param array $expected
   *   The expected result.
   * @param \Drupal\Core\Cache\CacheableMetadata $metadata
   *   The expected cache metadata object.
   */
  protected function assertResults($query, $variables, $expected, CacheableMetadata $metadata) {
    $result = $this->graphQlProcessor()->processQuery(
      $this->getDefaultSchema(),
      OperationParams::create([
        'query' => $query,
        'variables' => $variables,
      ])
    );

    $this->assertResultErrors($result, []);
    $this->assertResultData($result, $expected);
    $this->assertResultMetadata($result, $metadata);
  }

  /**
   * Assert a query result with certain errors.
   *
   * @param string $query
   *   The query string.
   * @param array $variables
   *   The query variables.
   * @param mixed $expected
   *   The expected error messages.
   * @param \Drupal\Core\Cache\CacheableMetadata $metadata
   *   The expected cache metadata object.
   */
  protected function assertErrors($query, $variables, $expected, CacheableMetadata $metadata) {
    $result = $this->graphQlProcessor()->processQuery(
      $this->getDefaultSchema(),
      OperationParams::create([
        'query' => $query,
        'variables' => $variables,
      ])
    );

    $this->assertResultErrors($result, $expected);
    $this->assertResultMetadata($result, $metadata);
  }

  /**
   * Assert a certain result data set on a query result.
   *
   * @param \Drupal\graphql\GraphQL\Execution\QueryResult $result
   *   The query result object.
   * @param mixed $expected
   *   The expected result data set.
   *
   * @internal
   */
  private function assertResultData(QueryResult $result, $expected) {
    $data = $result->toArray();
    $this->assertArrayHasKey('data', $data, 'No result data.');
    $this->assertEquals($expected, $data['data'], 'Unexpected query result.');
  }

  /**
   * Assert that the result contains contains a certain set of errors.
   *
   * @param \Drupal\graphql\GraphQL\Execution\QueryResult $result
   *   The query result object.
   * @param array $expected
   *   The list of expected error messages. Also allows regular expressions.
   *
   * @internal
   */
  private function assertResultErrors(QueryResult $result, array $expected) {
    // Retrieve the list of error strings.
    $errors = array_map(function (Error $error) {
      return $error->getMessage();
    }, $result->errors);

    // Initalize the status.
    $unexpected = [];
    $matchCount = array_map(function () {
      return 0;
    }, array_flip($expected));

    // Iterate through error messages.
    // Collect unmatched errors and count pattern hits.
    while ($error = array_pop($errors)) {
      $match = FALSE;
      foreach ($expected as $pattern) {
        if (@preg_match($pattern, NULL) === FALSE) {
          $match = $match || $pattern == $error;
          $matchCount[$pattern]++;
        }
        else {
          $match = $match || preg_match($pattern, $error);
          $matchCount[$pattern]++;
        }
      }

      if (!$match) {
        $unexpected[] = $error;
      }
    }

    // Create a list of patterns that never matched.
    $missing = array_keys(array_filter($matchCount, function ($count) {
      return $count == 0;
    }));

    $this->assertEquals([], $missing, "Missing errors:\n* " . implode("\n* ", $missing));
    $this->assertEquals([], $unexpected, "Unexpected errors:\n* " . implode("\n* ", $unexpected));
  }

  /**
   * Assert a certain set of result metadata on a query result.
   *
   * @param \Drupal\graphql\GraphQL\Execution\QueryResult $result
   *   The query result object.
   * @param \Drupal\Core\Cache\CacheableMetadata $expected
   *   The expected metadata object.
   *
   * @internal
   */
  private function assertResultMetadata(QueryResult $result, CacheableMetadata $expected) {
    $this->assertEquals($expected->getCacheMaxAge(), $result->getCacheMaxAge(), 'Unexpected cache max age.');

    $missingContexts = array_diff($expected->getCacheContexts(), $result->getCacheContexts());
    $this->assertEmpty($missingContexts, 'Missing cache contexts: ' . implode(', ', $missingContexts));

    $unexpectedContexts = array_diff($result->getCacheContexts(), $expected->getCacheContexts());
    $this->assertEmpty($unexpectedContexts, 'Unexpected cache contexts: ' . implode(', ', $unexpectedContexts));

    $missingTags = array_diff($expected->getCacheTags(), $result->getCacheTags());
    $this->assertEmpty($missingTags, 'Missing cache tags: ' . implode(', ', $missingTags));

    $unexpectedTags = array_diff($result->getCacheTags(), $expected->getCacheTags());
    $this->assertEmpty($unexpectedTags, 'Unexpected cache tags: ' . implode(', ', $unexpectedTags));
  }
}
