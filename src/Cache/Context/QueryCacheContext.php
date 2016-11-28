<?php

namespace Drupal\graphql\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Cache\Context\RequestStackCacheContextBase;

/**
 * Defines a cache context for GraphQL query caching.
 *
 * Cache context ID: 'graphql_query'.
 */
class QueryCacheContext extends RequestStackCacheContextBase implements CacheContextInterface {

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('GraphQL query');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $request = $this->requestStack->getCurrentRequest();

    $body = (array) json_decode($request->getContent()) + [
      'query' => NULL,
      'variables' => NULL,
    ];

    $query = $request->query->has('query') ? $request->query->get('query') : $body['query'];
    $query = $query ?: '';

    $variables = $request->query->has('variables') ? $request->query->get('variables') : $body['variables'];
    $variables = serialize($variables ?: []);

    return hash('sha256', "$query:$variables");
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
