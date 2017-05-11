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

    if ($request->attributes->has('query')) {
      $query = preg_replace('/\s{2,}/', ' ', $request->attributes->get('query') ?: '');
      $variables = $request->attributes->get('variables') ?: [];
      ksort($variables);

      return $this->contextCache[$request] = hash('sha256', json_encode([
        'query' => $query,
        'variables' => $variables,
      ]));
    }

    if ($request->attributes->has('queries')) {
      $queries = $request->attributes->get('queries');

      return $this->contextCache[$request] = hash('sha256', json_encode(array_map(function ($item) {
        $query = preg_replace('/\s{2,}/', ' ', !empty($item['query']) ? $item['query'] : '');
        $variables = !empty($item['variables']) ? $item['variables'] : [];
        ksort($variables);

        return [
          'query' => $query,
          'variables' => $variables,
        ];
      }, $queries)));
    }

    return $this->contextCache[$request] = '';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
