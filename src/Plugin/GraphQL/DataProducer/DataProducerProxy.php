<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use Drupal\graphql\GraphQL\Utility\DeferredUtility;
use Drupal\graphql\Plugin\DataProducerPluginCachingInterface;
use Drupal\graphql\Plugin\DataProducerPluginInterface;
use Drupal\graphql\Plugin\DataProducerPluginManager;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * A proxy class that lazy resolves data producers and has a result cache.
 */
class DataProducerProxy implements ResolverInterface {

  /**
   * If results should be cached.
   */
  protected bool $cached = FALSE;

  /**
   * Construct DataProducerProxy object.
   *
   * @param string $id
   *   The plugin id.
   * @param array $mapping
   *   The mapping of names to resolvers.
   * @param array $config
   *   The plugin config.
   * @param \Drupal\graphql\Plugin\DataProducerPluginManager $pluginManager
   *   The plugin manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack for looking up request time.
   * @param \Drupal\Core\Cache\Context\CacheContextsManager $contextsManager
   *   The cache context manager for cache keys.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend for results.
   */
  public function __construct(
    protected string $id,
    protected array $mapping,
    protected array $config,
    protected DataProducerPluginManager $pluginManager,
    protected RequestStack $requestStack,
    protected CacheContextsManager $contextsManager,
    protected CacheBackendInterface $cacheBackend,
  ) {
  }

  /**
   * Create a new data producer proxy.
   *
   * @return mixed
   *   A new data producer proxy instance created via the plugin manager.
   */
  public static function create(string $id, array $mapping = [], array $config = []): mixed {
    $manager = \Drupal::service('plugin.manager.graphql.data_producer');
    return $manager->proxy($id, $mapping, $config);
  }

  /**
   * Store a resolver for a given name.
   *
   * @return $this
   */
  public function map(string $name, ResolverInterface $mapping) {
    $this->mapping[$name] = $mapping;
    return $this;
  }

  /**
   * Set the cached flag.
   *
   * @return $this
   */
  public function cached(bool $cached = TRUE) {
    $this->cached = $cached;
    return $this;
  }

  /**
   * Resolve field value.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *
   * @return mixed
   *   The resolved field value from the data producer plugin.
   */
  public function resolve(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed {
    $plugin = $this->prepare($value, $args, $context, $info, $field);

    return DeferredUtility::returnFinally($plugin, function (DataProducerPluginInterface $plugin) use ($context, $field) {
      foreach ($plugin->getContexts() as $item) {
        /** @var \Drupal\Core\Plugin\Context\Context $item */
        if ($item->getContextDefinition()->isRequired() && !$item->hasContextValue()) {
          return NULL;
        }
      }

      if ($this->cached && $plugin instanceof DataProducerPluginCachingInterface) {
        if (!!$context->getServer()->get('caching')) {
          return $this->resolveCached($plugin, $context, $field);
        }
      }

      return $this->resolveUncached($plugin, $context, $field);
    });
  }

  /**
   * Instantiate the actual data producer and populate it with context values.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Exception
   */
  protected function prepare(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed {
    /** @var \Drupal\graphql\Plugin\DataProducerPluginInterface $plugin */
    $plugin = $this->pluginManager->createInstance($this->id, $this->config);
    $contexts = $plugin->getContextDefinitions();

    $values = [];
    foreach ($contexts as $name => $definition) {
      $mapper = $this->mapping[$name] ?? NULL;
      if ($definition->isRequired() && empty($mapper)) {
        throw new \LogicException(sprintf('Missing input mapper for argument %s.', $name));
      }

      if (!empty($mapper) && !($mapper instanceof ResolverInterface)) {
        throw new \Exception(sprintf('Invalid input mapper for argument %s.', $name));
      }

      $values[$name] = !empty($mapper) ? $mapper->resolve($value, $args, $context, $info, $field) : NULL;
    }

    $values = DeferredUtility::waitAll($values);
    return DeferredUtility::returnFinally($values, function ($values) use ($plugin) {
      foreach ($values as $name => $value) {
        $plugin->setContextValue($name, $value);
      }

      return $plugin;
    });
  }

  /**
   * Invoke the data producer directly.
   */
  protected function resolveUncached(DataProducerPluginInterface $plugin, ResolveContext $context, FieldContext $field): mixed {
    $output = $plugin->resolveField($field);
    return DeferredUtility::applyFinally($output, function () use ($plugin, $field): void {
      $field->addCacheableDependency($plugin);
    });
  }

  /**
   * Try to return a value from cache, otherwise invoke data producer.
   */
  protected function resolveCached(DataProducerPluginCachingInterface $plugin, ResolveContext $context, FieldContext $field): mixed {
    $prefix = $this->edgeCachePrefix($plugin);
    if ($cache = $this->cacheRead($prefix)) {
      [$value, $metadata] = $cache;
      $field->addCacheableDependency($metadata);
      return $value;
    }

    $output = $this->resolveUncached($plugin, $context, $field);
    return DeferredUtility::applyFinally($output, function ($value) use ($field, $prefix): void {
      $this->cacheWrite($prefix, $value, $field);
    });
  }

  /**
   * Calculates a cache prefix.
   */
  protected function edgeCachePrefix(DataProducerPluginCachingInterface $plugin): string {
    try {
      $prefix = $plugin->edgeCachePrefix();
    }
    catch (\Exception $e) {
      throw new \LogicException(sprintf('Failed to serialize edge cache vectors for plugin %s.', $plugin->getPluginId()));
    }

    $contexts = $plugin->getCacheContexts();
    $keys = $this->contextsManager->convertTokensToKeys($contexts)->getKeys();
    return md5(serialize([$plugin->getPluginId(), $prefix, $keys]));
  }

  /**
   * Cache lookup.
   *
   * @return array|null
   *   The cached data containing value and metadata, or NULL if no cache entry
   *   found.
   */
  protected function cacheRead(string $prefix): ?array {
    if ($cache = $this->cacheBackend->get("$prefix:context")) {
      $keys = !empty($cache->data) ? $this->contextsManager->convertTokensToKeys($cache->data)->getKeys() : [];
      $keys = serialize($keys);

      if (($cache = $this->cacheBackend->get("$prefix:result:$keys")) && $data = $cache->data) {
        return $data;
      }
    }

    return NULL;
  }

  /**
   * Store result values in cache.
   */
  protected function cacheWrite(string $prefix, mixed $value, FieldContext $field): void {
    // Bail out early if the field context is already uncacheable.
    if ($field->getCacheMaxAge() === 0) {
      return;
    }

    $metadata = new CacheableMetadata();
    $metadata->addCacheableDependency($field);

    // Do not add the cache contexts from the result value because they are not
    // known at fetch time and would render the written cache unusable.
    if ($value instanceof CacheableDependencyInterface) {
      $metadata->addCacheTags($value->getCacheTags());
      $metadata->mergeCacheMaxAge($value->getCacheMaxAge());
    }

    if ($metadata->getCacheMaxAge() === 0) {
      return;
    }

    $expire = $this->maxAgeToExpire($metadata->getCacheMaxAge());
    $tags = $metadata->getCacheTags();
    $tokens = $metadata->getCacheContexts();

    $keys = !empty($tokens) ? $this->contextsManager->convertTokensToKeys($tokens)->getKeys() : [];
    $keys = serialize($keys);

    $this->cacheBackend->setMultiple([
      "$prefix:context" => [
        'data' => $tokens,
        'expire' => $expire,
        'tags' => $tags,
      ],
      "$prefix:result:$keys" => [
        'data' => [$value, $metadata],
        'expire' => $expire,
        'tags' => $tags,
      ],
    ]);
  }

  /**
   * Maps a cache max age value to an "expire" value for the Cache API.
   *
   * @return int
   *   A corresponding "expire" value.
   *
   * @see \Drupal\Core\Cache\CacheBackendInterface::set()
   */
  protected function maxAgeToExpire(int $maxAge): int {
    $time = $this->requestStack->getMainRequest()->server->get('REQUEST_TIME');
    return ($maxAge === Cache::PERMANENT) ? Cache::PERMANENT : (int) $time + $maxAge;
  }

}
