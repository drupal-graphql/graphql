<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\PersistedQuery;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Attribute\PersistedQuery;
use Drupal\graphql\PersistedQuery\PersistedQueryPluginBase;
use GraphQL\Error\Error;
use GraphQL\Server\OperationParams;
use GraphQL\Server\RequestError;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generates IDs for queries and loads persisted queries from the cache.
 */
#[PersistedQuery(
  id: "automatic_persisted_query",
  label: "Automatic Persisted Query",
  description: "Load persisted queries from the cache."
)]
class AutomaticPersistedQuery extends PersistedQueryPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    protected CacheBackendInterface $cache,
    protected KillSwitch $pageCacheKillSwitch,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cache.graphql.apq'),
      $container->get('page_cache_kill_switch')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery($id, OperationParams $operation): ?string {
    if ($query = $this->cache->get($id)) {
      return $query->data;
    }
    // Cache miss - store the query in cache.
    $query = $operation->query;
    $queryHash = $operation->extensions['persistedQuery']['sha256Hash'] ?? '';

    if (is_string($queryHash) && $queryHash !== '' && is_string($query)) {
      // If we have a query and the hash matches then we can cache it.
      $computedQueryHash = hash('sha256', $query);
      if ($queryHash !== $computedQueryHash) {
        throw new Error('Provided sha does not match query');
      }
      $this->cache->set($queryHash, $query);
      return $query;
    }
    // Preventing page cache for this request. Otherwise, we would need to add
    // a cache tag to the response and flush it when we add the persisted
    // query. This is not necessary, because the PersistedQueryNotFound
    // response is very short-lived.
    $this->pageCacheKillSwitch->trigger();
    throw new RequestError('PersistedQueryNotFound');
  }

}
