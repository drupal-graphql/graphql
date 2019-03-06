<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Utility\DeferredUtility;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\graphql\Plugin\DataProducerPluginManager;

class DataProducerProxy implements DataProducerInterface {

  /**
   * Construct DataProducerProxy object.
   * @param string $id      DataProducer plugin id.
   * @param array $config   Plugin configuration.
   * @param \Drupal\graphql\Plugin\DataProducerPluginManager $manager DataProducer manager.
   */
  public function __construct($id, $config, DataProducerPluginManager $manager) {
    $this->id = $id;
    $this->config = $config;
    $this->manager = $manager;
    $this->plugin = NULL;
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
   * @inheritdoc.
   */
  public function resolve($value, $args, $context, $info) {
    $values = DeferredUtility::waitAll($this->getInputValues($value, $args, $context, $info));
    return DeferredUtility::returnFinally($values, function ($values) use ($context, $info) {
      $metadata = new CacheableMetadata();
      $metadata->addCacheContexts(['user.permissions']);

      // TODO: check lookup.
      $output = $this->shouldLookupEdgeCache($values, $context, $info) ?
        $this->resolveCached($values, $context, $info, $metadata) :
        $this->resolveUncached($values, $context, $info, $metadata);

      return DeferredUtility::applyFinally($output, function () use ($context, $metadata) {
        $context->addCacheableDependency($metadata);
      });
    });
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

  protected function getArguments() {
    // TODO: implement instead of using getInputValues.
  }

  /**
   * @param $value
   * @param $args
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return array|\GraphQL\Deferred
   * @throws \Exception
   */
  protected function getInputValues($value, $args, ResolveContext $context, ResolveInfo $info) {
    $values = [];

    $plugin = $this->getPlugin();
    $definitions = $plugin->getPluginDefinition();

    $consumes = isset($definitions['consumes']) ? $definitions['consumes'] : [];
    foreach ($consumes as $key => $definition) {
      if ($definition->isRequired() && !$this->hasInputMapper($key)) {
        throw new \Exception(sprintf('Missing input data mapper for %s on field %s on type %s.', $key, $info->fieldName, $info->parentType->name));
      }

      $mapper = $this->getInputMapper($key);

      if (isset($mapper) && !$mapper instanceof DataProducerCallable) {
        throw new \Exception(sprintf('Invalid input mapper for %s on field %s on type %s. Input mappers need to be callable.', $key, $info->fieldName, $info->parentType->name));
      }

      $values[$key] = isset($mapper) ? $mapper->resolve($value, $args, $context, $info) : NULL;
      if ($definition->isRequired() && !isset($values[$key])) {
        throw new \Exception(sprintf('Missing input data for %s on field %s on type %s.', $key, $info->fieldName, $info->parentType->name));
      }
    }

    return $values;
  }

  /**
   * @param $from
   *
   * @return boolean
   */
  protected function hasInputMapper($from) {
    if (!($this->plugin instanceof ConfigurablePluginInterface)) {
      return FALSE;
    }

    return isset($this->plugin->getConfiguration()['mapping'][$from]);
  }

  /**
   * @param $from
   *
   * @return callable|null
   */
  protected function getInputMapper($from) {
    return $this->plugin->getConfiguration()['mapping'][$from] ?? NULL;
  }

}
