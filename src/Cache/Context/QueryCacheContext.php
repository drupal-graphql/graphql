<?php

namespace Drupal\graphql\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines the QueryCacheContext service, for "per query" caching.
 *
 * Cache context ID: 'gql'.
 */
class QueryCacheContext implements CacheContextInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Static cache of hashed cache contexts.
   *
   * @var \SplObjectStorage
   */
  protected $contextCache;

  /**
   * Constructs a new QueryCacheContext class.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
    $this->contextCache = new \SplObjectStorage();
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Query');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $request = $this->requestStack->getCurrentRequest();
    if (isset($this->contextCache[$request])) {
      return $this->contextCache[$request];
    }

    $hash = '';
    if ($request->attributes->has('query')) {
      $hash = $this->contextCache[$request] = $this->getHash(
        $request->attributes->get('query') ?: '',
        $request->attributes->get('variables') ?: []
      );
    }
    else if ($request->attributes->has('queries')) {
      $queries = $request->attributes->get('queries');

      $hash = hash('sha256', json_encode(array_map(function ($item) {
        return $this->getHash(
          !empty($item['query']) ? $item['query'] : '',
          !empty($item['variables']) ? $item['variables'] : []
        );
      }, $queries)));
    }

    return $this->contextCache[$request] = $hash;
  }

  /**
   * Produces an optimized hashed string of the query and variables.
   *
   * Sorts the variables by their key and eliminates whitespace from the query
   * to enable better reuse of the cache entries.
   *
   * @param string $query
   *   The graphql query string.
   * @param array $variables
   *   The graphql query variables.
   *
   * @return string
   *   The hashed string containing.
   */
  protected function getHash($query = '', array $variables = []) {
    $query = preg_replace('/\s{2,}/', ' ', $query);
    ksort($variables);

    return hash('sha256', json_encode([
      'query' => $query,
      'variables' => $variables,
    ]));
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
