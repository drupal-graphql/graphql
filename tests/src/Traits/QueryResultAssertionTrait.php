<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\Execution\QueryResult;

trait QueryResultAssertionTrait {

  protected $activeSchema = 'default';

  /**
   * @return \Drupal\graphql\GraphQL\Execution\QueryProcessor
   */
  protected function getGraphQLProcessor() {
    return $this->container->get('graphql.query_processor');
  }

  protected function defaultCacheTags() {
    return ['graphql_response', 'graphql_schema'];
  }

  protected function defaultCacheContexts() {
    return ['gql', 'languages:language_interface', 'user'];
  }

  protected function defaultCacheMaxAge() {
    return Cache::PERMANENT;
  }

  protected function defaultCacheMetaData() {
    $metadata = new CacheableMetadata();
    $metadata->setCacheMaxAge($this->defaultCacheMaxAge());
    $metadata->setCacheTags($this->defaultCacheTags());
    $metadata->setCacheContexts($this->defaultCacheContexts());
    return $metadata;
  }

  /**
   * @param $query
   * @param $variables
   * @param $result
   * @param \Drupal\Core\Cache\CacheableMetadata $metadata
   */
  protected function assertResults($query, $variables, $expected, CacheableMetadata $metadata) {
    $result = $this->getGraphQLProcessor()->processQuery(
      $this->activeSchema,
      $query,
      $variables
    );

    $this->assertResultErrors($result, []);
    $this->assertResultData($result, $expected);
    $this->assertResultMetadata($result, $metadata);
  }

  protected function assertErrors($query, $variables, $expected, CacheableMetadata $metadata) {
    $result = $this->getGraphQLProcessor()->processQuery(
      $this->activeSchema,
      $query,
      $variables
    );

    $this->assertResultErrors($result, $expected);
    $this->assertResultMetadata($result, $metadata);
  }

  private function assertResultData(QueryResult $result, array $expected) {
    $data = $result->getData();
    $this->assertArrayHasKey('data', $data, 'No result data.');
    $this->assertEquals($expected, $data['data'], 'Unexpected query result.');
  }

  /**
   * Assert that the result contains no errors.
   *
   * @param array $data
   *   The query result.
   */
  private function assertResultErrors(QueryResult $result, array $expected) {
    $data = $result->getData();
    $errors = array_map(function($error) {
      return $error['message'];
    }, array_key_exists('errors', $data) ? $data['errors'] : []);
    $this->assertEquals($expected, $errors, 'Invalid GraphQL query. Errors: ' . implode("\n* ", $errors));
  }

  private function assertResultMetadata(QueryResult $result, CacheableMetadata $expected) {
    if (!$expected) {
      $expected = new CacheableMetadata();
    }
    $this->assertEquals($expected->getCacheMaxAge(), $result->getCacheMaxAge(), 'Unexpected cache max age.');
    $this->assertEquals($expected->getCacheContexts(), $result->getCacheContexts(), 'Unexpected cache contexts.');
    $this->assertEquals($expected->getCacheTags(), $result->getCacheTags(), 'Unexpected cache tags.');
  }
}