<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerProxy;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collects data producer plugins that are composed to read and write data.
 */
class DataProducerPluginManager extends DefaultPluginManager {

  /**
   * The request stack later used to get the request time.
   */
  protected RequestStack $requestStack;

  /**
   * The cache context manager for calculating cache keys.
   */
  protected CacheContextsManager $contextsManager;

  /**
   * The cache backend to cache results in.
   */
  protected CacheBackendInterface $resultCacheBackend;

  /**
   * DataProducerPluginManager constructor.
   */
  public function __construct(
    bool|string $pluginSubdirectory,
    \Traversable $namespaces,
    ModuleHandlerInterface $moduleHandler,
    CacheBackendInterface $definitionCacheBackend,
    RequestStack $requestStack,
    CacheContextsManager $contextsManager,
    CacheBackendInterface $resultCacheBackend,
    ?string $pluginInterface,
    string $pluginAnnotationName,
    array $config,
  ) {
    parent::__construct(
      $pluginSubdirectory,
      $namespaces,
      $moduleHandler,
      $pluginInterface,
      $pluginAnnotationName
    );

    $this->alterInfo('graphql_data_producer');
    $this->useCaches(empty($config['development']));
    $this->setCacheBackend($definitionCacheBackend, 'graphql_data_producer', ['graphql_data_producer']);

    $this->requestStack = $requestStack;
    $this->contextsManager = $contextsManager;
    $this->resultCacheBackend = $resultCacheBackend;
  }

  /**
   * Creates a data producer proxy that lazy forwards resolve requests.
   *
   * The data producer with the given ID is wrapped.
   */
  public function proxy(string $id, array $mapping = [], array $config = []): DataProducerProxy {
    return new DataProducerProxy(
      $id,
      $mapping,
      $config,
      $this,
      $this->requestStack,
      $this->contextsManager,
      $this->resultCacheBackend
    );
  }

}
