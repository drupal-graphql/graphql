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
   * DataProducerPluginManager constructor.
   *
   * @param bool|string $pluginSubdirectory
   *   The namespace-relative path to the plugin sub-directory.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $definitionCacheBackend
   *   The cache backend to use to load plugin information.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   * @param \Drupal\Core\Cache\Context\CacheContextsManager $contextsManager
   *   The cache contexts manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $resultCacheBackend
   *   The cache backend for data producer result caching.
   * @param string|null $pluginInterface
   *   (optional) The interface each plugin should implement.
   * @param string $pluginAttributeName
   *   The name of the provider attribute to search for in plugin definitions.
   * @param string $pluginAnnotationName
   *   The name of the annotation to search for in plugin definitions.
   * @param array $config
   *   The configuration service parameters.
   */
  public function __construct(
    bool|string $pluginSubdirectory,
    \Traversable $namespaces,
    ModuleHandlerInterface $moduleHandler,
    CacheBackendInterface $definitionCacheBackend,
    protected RequestStack $requestStack,
    protected CacheContextsManager $contextsManager,
    protected CacheBackendInterface $resultCacheBackend,
    ?string $pluginInterface,
    string $pluginAttributeName,
    string $pluginAnnotationName,
    array $config,
  ) {
    parent::__construct(
      $pluginSubdirectory,
      $namespaces,
      $moduleHandler,
      $pluginInterface,
      $pluginAttributeName,
      $pluginAnnotationName
    );

    $this->alterInfo('graphql_data_producer');
    $this->useCaches(empty($config['development']));
    $this->setCacheBackend($definitionCacheBackend, 'graphql_data_producer', ['graphql_data_producer']);
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
