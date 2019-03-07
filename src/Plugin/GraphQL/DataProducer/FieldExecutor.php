<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

use Drupal\Core\Cache\Cache;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\Utility\DeferredUtility;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Entity\EntityInterface;

class FieldExecutor {

  /**
   * Construct FieldExecutor object.
   *
   * @param [type] $id      [description]
   * @param [type] $config  [description]
   * @param [type] $manager [description]
   */
  public function __construct($id, $config, $manager) {
    $this->id = $id;
    $this->config = $config;
    $this->manager = $manager;
    $this->plugin = NULL;
  }

  /**
   * Resolve field value.
   */
  public function resolve($values, ResolveContext $context, ResolveInfo $info, CacheableMetadata $metadata) {
    $output = $this->shouldLookupEdgeCache($values, $context, $info) ?
      $this->resolveCached($values, $context, $info, $metadata) :
      $this->resolveUncached($values, $context, $info, $metadata);
    return $output;
  }

  /**
   * Return DataProducerPlugin.
   * @return \Drupal\graphql\Plugin\GraphQL\DataProducerInterface DataProducer object.
   */
  public function getPlugin() {
    if (!$this->plugin) {
      $this->plugin = $this->manager->getInstance(['id' => $this->id, 'configuration' => $this->config]);
    }
    return $this->plugin;
  }

  /**
   * @return \Symfony\Component\HttpFoundation\RequestStack
   */
  protected function getRequestStack() {
    if (!isset($this->requestStack)) {
      $this->requestStack = \Drupal::service('request_stack');
    }

    return $this->requestStack;
  }

  /**
   * @return \Drupal\Core\Cache\CacheBackendInterface
   */
  protected function getCacheBackend() {
    if (!isset($this->cacheBackend)) {
      $this->cacheBackend = \Drupal::service('cache.graphql.results');
    }

    return $this->cacheBackend;
  }

  /**
   * @return \Drupal\Core\Cache\Context\CacheContextsManager
   */
  protected function getCacheContextsManager() {
    if (!isset($this->cacheContextsManager)) {
      $this->cacheContextsManager = \Drupal::service('cache_contexts_manager');
    }

    return $this->cacheContextsManager;
  }

 /**
   * @param array $values
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context Resolve context.
   * @param \GraphQL\Type\Definition\ResolveInfo $info Resolve info.
   *
   * @return bool
   */
  protected function shouldLookupEdgeCache(array $values, ResolveContext $context, ResolveInfo $info) {
    // Use cache configs from the schema definition, not from the plugin.
    return array_key_exists('cache', $this->config) && $this->config['cache'];
  }

  /**
   * @param $values
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *
   * @return mixed
   */
  protected function resolveUncached($values, ResolveContext $context, ResolveInfo $info, RefinableCacheableDependencyInterface $metadata) {
    $plugin = $this->getPlugin();

    $output = call_user_func_array([$plugin, 'resolve'], array_merge($values, [$metadata]));
    return DeferredUtility::applyFinally($output, function ($value) use ($metadata) {
      if ($value instanceof CacheableDependencyInterface) {
        $metadata->addCacheableDependency($value);
      }
    });
  }

  /**
   * TODO: Wrap the cache lookup in a deferred resolver.
   * TODO: Postpone and batch cache writing at the end of the query processing.
   * TODO: ?Move it to DataProducer base plugin implementation?
   *
   * @param $values
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *
   * @return mixed
   */
  protected function resolveCached($values, ResolveContext $context, ResolveInfo $info, RefinableCacheableDependencyInterface $metadata) {
    if (!$prefix = $this->getCachePrefix($values, $context, $info)) {
      throw new \LogicException('Failed to generate cache prefix.');
    }

    if ($cache = $this->cacheRead($prefix)) {
      $metadata->addCacheableDependency($cache['metadata']);
      return $this->unserializeCache($cache['value']);
    }
    $output = $this->resolveUncached($values, $context, $info, $metadata);
    return DeferredUtility::applyFinally($output, function ($value) use ($values, $context, $info, $metadata, $prefix) {
      if ($metadata->getCacheMaxAge() === 0 || !$this->shouldWriteEdgeCache($value, $values, $context, $info)) {
        return;
      }

      $this->cacheWrite($prefix, $value, $metadata);
    });
  }

  /**
   * @param $prefix
   *
   * @return array|null
   */
  protected function cacheRead($prefix) {
    $backend = $this->getCacheBackend();

    if ($cache = $backend->get("$prefix:context")) {
      $manager = $this->getCacheContextsManager();
      $keys = serialize(!empty($cache->data) ? $manager->convertTokensToKeys($cache->data)->getKeys() : []);

      if (($cache = $backend->get("$prefix:result:$keys")) && $data = $cache->data) {
        return $data;
      }
    }

    return NULL;
  }

  /**
   * @param $prefix
   *
   * @param $value
   * @param \Drupal\Core\Cache\CacheableDependencyInterface $metadata
   */
  protected function cacheWrite($prefix, $value, CacheableDependencyInterface $metadata) {
    $manager = $this->getCacheContextsManager();
    $backend = $this->getCacheBackend();

    $expire = $this->maxAgeToExpire($metadata->getCacheMaxAge());
    $tags = $metadata->getCacheTags();
    $tokens = $metadata->getCacheContexts();

    $keys = serialize(!empty($tokens) ? $manager->convertTokensToKeys($tokens)->getKeys() : []);
    $data = $this->serializeCache($value);

    $backend->setMultiple([
      "$prefix:context" => [
        'data' => $tokens,
        'expire' => $expire,
        'tags' => $tags,
      ],
      "$prefix:result:$keys" => [
        'data' => ['value' => $data, 'metadata' => $metadata],
        'expire' => $expire,
        'tags' => $tags,
      ],
    ]);
  }

  /**
   * @param array $values
   *
   * @return bool
   */
  protected function shouldWriteEdgeCache($result, array $values, ResolveContext $context, ResolveInfo $info) {
    // This function is only ever called if we are in a cached lookup. Hence, we
    // default to always returning TRUE. This results in any failed cache lookup
    // to always also write to the cache after resolving the result from
    // scratch but only, if the plugin looks up the cache in the first place.
    return TRUE;
  }

  /**
   * @param array $values
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return string
   */
  protected function getCachePrefix(array $values, ResolveContext $context, ResolveInfo $info) {
    $vectors = json_encode($this->getCacheVectors($values, $context, $info));
    $plugin = $this->id;
    return md5("$plugin:$vectors");
  }

  /**
   * @param array $values
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return string
   */
  protected function getCacheVectors(array $values, ResolveContext $context, ResolveInfo $info) {
    // TODO: Also serialize the configuration array.
    return array_map(function ($value) {
      if (is_scalar($value) || !isset($value)) {
        return $value;
      }

      if (is_array($value) || $value instanceof \stdClass) {
        return json_encode($value);
      }

      if ($value instanceof EntityInterface) {
        return "{$value->getEntityTypeId()}:{$value->id()}";
      }

      throw new \LogicException('Could not extract raw value from input.');
    }, $values);
  }

  /**
   * @param mixed $value
   *
   * @return mixed
   */
  protected function unserializeCache($value) {
    return $value;
  }

  /**
   * @param mixed $value
   *
   * @return mixed
   */
  protected function serializeCache($value) {
    return $value;
  }

  /**
   * Maps a cache max age value to an "expire" value for the Cache API.
   *
   * @param int $maxAge
   *
   * @return int
   *   A corresponding "expire" value.
   *
   * @see \Drupal\Core\Cache\CacheBackendInterface::set()
   */
  protected function maxAgeToExpire($maxAge) {
    $time = $this->getRequestStack()->getMasterRequest()->server->get('REQUEST_TIME');
    return ($maxAge === Cache::PERMANENT) ? Cache::PERMANENT : (int) $time + $maxAge;
  }

}
