<?php

namespace Drupal\graphql\GraphQL\Schema;

use Drupal\graphql\GraphQL\CacheableEdgeInterface;
use Drupal\graphql\GraphQL\Utility\TypeCollector;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Type\InputObject\AbstractInputObjectType;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

/**
 * Loads and caches a generated GraphQL schema.
 */
class SchemaLoader {

  /**
   * The cache contexts manager service.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager
   */
  protected $contextsManager;

  /**
   * The schema plugin manager service.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\SchemaPluginManager
   */
  protected $schemaManager;

  /**
   * The schema cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $schemaCache;

  /**
   * The cache metadata cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $metadataCache;

  /**
   * The service configuration.
   *
   * @var array
   */
  protected $config;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Static cache of loaded schemas.
   *
   * @var \Youshido\GraphQL\Schema\AbstractSchema[]
   */
  protected $schemas = [];

  /**
   * Static cache of loaded cache metadata.
   *
   * @var \Drupal\Core\Cache\RefinableCacheableDependencyInterface[]
   */
  protected $metadata = [];

  /**
   * SchemaLoader constructor.
   *
   * @param \Drupal\Core\Cache\Context\CacheContextsManager $contextsManager
   *   The cache contexts manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   * @param \Drupal\graphql\Plugin\GraphQL\SchemaPluginManager $schemaManager
   *   The schema plugin manager service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $schemaCache
   *   The schema cache backend.
   * @param \Drupal\Core\Cache\CacheBackendInterface $metadataCache
   *   The metadata cache backend.
   * @param array $config
   *   The configuration provided through the services.yml.
   */
  public function __construct(
    CacheContextsManager $contextsManager,
    RequestStack $requestStack,
    SchemaPluginManager $schemaManager,
    CacheBackendInterface $schemaCache,
    CacheBackendInterface $metadataCache,
    array $config
  ) {
    $this->config = $config;
    $this->schemaManager = $schemaManager;
    $this->contextsManager = $contextsManager;
    $this->schemaCache = $schemaCache;
    $this->metadataCache = $metadataCache;
    $this->requestStack = $requestStack;
  }

  /**
   * Loads and caches the generated schema.
   *
   * @param string $name
   *   The name of the schema to load.
   *
   * @return \Youshido\GraphQL\Schema\AbstractSchema
   *   The generated GraphQL schema.
   */
  public function getSchema($name) {
    if (($schema = &$this->schemas[$name]) !== NULL) {
      return $schema;
    }

    $schemaCache = !empty($this->config['schema_cache']);
    if (!empty($schemaCache) && ($cache = $this->metadataCache->get($name)) && $cache->data) {
      $cid = $this->getCacheIdentifier($name, $cache->data);

      if (($cache = $this->schemaCache->get($cid)) && $cache->data) {
        return $schema = $cache->data;
      }
    }

    // Get the schema object from the plugin instance.
    $schema = $this->schemaManager->createInstance($name)->getSchema();
    if (empty($schemaCache)) {
      return $schema;
    }

    $metadata = $this->getCacheMetadata($name);
    if ($metadata->getCacheMaxAge() !== 0) {
      $tags = $metadata->getCacheTags();
      $expire = $this->maxAgeToExpire($metadata->getCacheMaxAge());
      $cid = $this->getCacheIdentifier($name, $metadata);

      // Write the cache entry for the schema cache entries.
      $this->schemaCache->set($cid, $schema, $expire, $tags);
    }

    return $schema;
  }

  /**
   * Retrieves the schema's cache metadata.
   *
   * @param string $name
   *   The name of the schema.
   * @return \Drupal\Core\Cache\RefinableCacheableDependencyInterface
   *   The cache metadata for the schema.
   */
  public function getCacheMetadata($name) {
    if (($metadata = &$this->metadata[$name]) !== NULL) {
      return $metadata;
    }

    $schemaCache = !empty($this->config['schema_cache']);
    if (!empty($schemaCache) && ($metadataCache = $this->metadataCache->get($name)) && $metadataCache->data) {
      return $metadata = $metadataCache->data;
    }

    /** @var \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata */
    $schema = $this->getSchema($name);
    $metadata = $this->extractCacheMetadata($schema);
    $metadata->addCacheTags(['graphql_schema']);

    // Make the schema uncacheable if it is configured that way.
    if (empty($schemaCache)) {
      $metadata->mergeCacheMaxAge(0);
    }

    if (($maxAge = $metadata->getCacheMaxAge()) !== 0) {
      $tags = $metadata->getCacheTags();
      $expire = $this->maxAgeToExpire($maxAge);

      // Write the cache entry for the schema cache metadata.
      $this->metadataCache->set($name, $metadata, $expire, $tags);
    }

    return $metadata;
  }

  /**
   * Collects schema cache metadata from all types registered with the schema.
   *
   * The cache metadata is statically cached. This means that the schema may not
   * be modified after this method has been called.
   *
   * @param \Youshido\GraphQL\Schema\AbstractSchema $schema
   *   The schema to extract the cache metadata from.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cache metadata collected from the schema's types.
   */
  protected function extractCacheMetadata(AbstractSchema $schema) {
    $metadata = new CacheableMetadata();
    foreach (TypeCollector::collectTypes($schema) as $type) {
      if ($type instanceof CacheableEdgeInterface) {
        $metadata->addCacheableDependency($type->getSchemaCacheMetadata());
      }

      if ($type instanceof AbstractObjectType || $type instanceof AbstractInputObjectType || $type instanceof AbstractInterfaceType) {
        foreach ($type->getFields() as $field) {
          if ($field instanceof CacheableEdgeInterface) {
            $metadata->addCacheableDependency($field->getSchemaCacheMetadata());
          }
        }
      }
    }

    return $metadata;
  }

  /**
   * Maps a max age value to an "expire" value for the Cache API.
   *
   * @param int $maxAge
   *   A max age value.
   *
   * @return int
   *   A corresponding "expire" value.
   *
   * @see \Drupal\Core\Cache\CacheBackendInterface::set()
   */
  protected function maxAgeToExpire($maxAge) {
    if ($maxAge === Cache::PERMANENT) {
      return Cache::PERMANENT;
    }

    return (int) $this->requestStack->getMasterRequest()->server->get('REQUEST_TIME') + $maxAge;
  }

  /**
   * Generates a cache identifier for the passed cache contexts.
   *
   * @param string $name
   *   The name of the schema.
   * @param \Drupal\Core\Cache\CacheableDependencyInterface $metadata
   *   Optional array of cache context tokens.
   *
   * @return string The generated cache identifier.
   *   The generated cache identifier.
   */
  protected function getCacheIdentifier($name, CacheableDependencyInterface $metadata) {
    $tokens = $metadata->getCacheContexts();
    $keys = $this->contextsManager->convertTokensToKeys($tokens)->getKeys();
    return implode(':', array_merge(['graphql', $name], array_values($keys)));
  }

}
