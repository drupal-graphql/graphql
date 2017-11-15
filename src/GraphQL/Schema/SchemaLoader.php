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
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginReferenceInterface;
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
   * @var \Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface[]
   */
  protected $schemas = [];

  /**
   * Static cache of loaded cache metadata.
   *
   * @var \Drupal\Core\Cache\RefinableCacheableDependencyInterface[]
   */
  protected $metadata = [];

  /**
   * Constructs a SchemaLoader object.
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
   * @return \Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface
   *   The generated GraphQL schema.
   */
  public function getSchema($name) {
    if (array_key_exists($name, $this->schemas)) {
      return $this->schemas[$name];
    }

    // The cache key is made up of all of the globally known cache contexts.
    if (!empty($this->config['schema_cache'])) {
      if (($contextCache = $this->metadataCache->get("$name:schema")) && $contextCache->data) {
        $cid = $this->getCacheIdentifier($name, $contextCache->data);

        if (($schema = $this->schemaCache->get($cid)) && $schema->data) {
          return $this->schemas[$name] = $schema->data;
        }
      }
    }

    $this->schemas[$name] = $this->schemaManager->createInstance($name)->getSchema();
    // If the schema is not cacheable, just return it directly.
    if (empty($this->config['schema_cache'])) {
      return $this->schemas[$name];
    }

    // Compute the cache identifier, tag and expiry time.
    $schemaCacheMetadata = $this->getSchemaCacheMetadata($name);
    if ($schemaCacheMetadata->getCacheMaxAge() !== 0) {
      $tags = $schemaCacheMetadata->getCacheTags();
      $expire = $this->maxAgeToExpire($schemaCacheMetadata->getCacheMaxAge());
      $cid = $this->getCacheIdentifier($name, $schemaCacheMetadata);

      // Write the cache entry for the schema cache entries.
      $this->schemaCache->set($cid, $this->schemas[$name], $expire, $tags);
    }

    return $this->schemas[$name];
  }

  /**
   * Retrieves the schema's cache metadata.
   *
   * @param string $name
   *   The name of the schema.
   * @return \Drupal\Core\Cache\CacheableDependencyInterface
   *   The cache metadata for the schema.
   */
  public function getSchemaCacheMetadata($name) {
    return $this->getCacheMetadata($name, "$name:schema", function (AbstractSchema $schema) {
      return $this->extractSchemaCacheMetadata($schema);
    });
  }

  /**
   * Retrieves the schema's response cache metadata.
   *
   * @param string $name
   *   The name of the schema.
   * @return \Drupal\Core\Cache\RefinableCacheableDependencyInterface
   *   The cache metadata for the schema's responses.
   */
  public function getResponseCacheMetadata($name) {
    return $this->getCacheMetadata($name, "$name:response", function (AbstractSchema $schema) {
      return $this->extractResponseCacheMetadata($schema);
    })->addCacheableDependency($this->getSchemaCacheMetadata($name));
  }

  /**
   * Helper function to load cache metadata from a schema.
   *
   * @param string $name
   *   The name of the schema.
   * @param string $cid
   *   The cache identifier for caching the metadata
   * @param callable $callback
   *   Callback to return the cache metadata from the schema.
   *
   * @return \Drupal\Core\Cache\RefinableCacheableDependencyInterface
   *   The cache metadata.
   */
  protected function getCacheMetadata($name, $cid, callable $callback) {
    if (array_key_exists($cid, $this->metadata)) {
      return $this->metadata[$cid];
    }

    // The cache key is made up of all of the globally known cache contexts.
    if (!empty($this->config['schema_cache'])) {
      if (($metadataCache = $this->metadataCache->get($cid)) && $metadataCache->data) {
        return $this->metadata[$name] = $metadataCache->data;
      }
    }

    /** @var \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata */
    $schema = $this->getSchema($name);
    $metadata = $callback($schema);
    $this->metadata[$cid] = $metadata;
    if (empty($this->config['schema_cache'])) {
      return $this->metadata[$cid];
    }

    // Use the schema cache metadata to determine cache expiry and tags.
    $schemaCacheMetadata = $this->getSchemaCacheMetadata($name);
    if ($schemaCacheMetadata->getCacheMaxAge() !== 0) {
      $tags = $schemaCacheMetadata->getCacheTags();
      $expire = $this->maxAgeToExpire($schemaCacheMetadata->getCacheMaxAge());

      // Write the cache entry for the response cache metadata.
      $this->metadataCache->set($cid, $metadata, $expire, $tags);
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
  protected function extractSchemaCacheMetadata(AbstractSchema $schema) {
    $metadata = new CacheableMetadata();
    $metadata->setCacheMaxAge(Cache::PERMANENT);
    $metadata->addCacheTags(['graphql_schema']);

    $metadata->addCacheableDependency($this->collectCacheMetadata($schema, function (CacheableEdgeInterface $item, AbstractSchema $schema) {
      return $item->getSchemaCacheMetadata($schema);
    }));

    return $metadata;
  }

  /**
   * Collects result cache metadata from all types registered with the schema.
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
  protected function extractResponseCacheMetadata(AbstractSchema $schema) {
    $metadata = new CacheableMetadata();
    $metadata->setCacheMaxAge(Cache::PERMANENT);
    $metadata->addCacheTags(['graphql_response']);
    $metadata->addCacheContexts(['gql']);

    $metadata->addCacheableDependency($this->collectCacheMetadata($schema, function (CacheableEdgeInterface $item, AbstractSchema $schema) {
      return $item->getResponseCacheMetadata($schema);
    }));

    return $metadata;
  }

  /**
   * Recursively collects cache metadata from the generated schema.
   *
   * @param \Youshido\GraphQL\Schema\AbstractSchema $schema
   *   The schema.
   * @param callable $extract
   *   Callback to extract cache metadata from a plugin within the schema.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The collected cache metadata.
   */
  protected function collectCacheMetadata(AbstractSchema $schema, callable $extract) {
    $metadata = new CacheableMetadata();

    foreach (TypeCollector::collectTypes($schema) as $type) {
      if ($type instanceof CacheableEdgeInterface) {
        $metadata->addCacheableDependency($extract($type, $schema));
      }

      if ($type instanceof AbstractObjectType || $type instanceof AbstractInputObjectType || $type instanceof AbstractInterfaceType) {
        foreach ($type->getFields() as $field) {
          if ($field instanceof CacheableEdgeInterface) {
            $metadata->addCacheableDependency($extract($field, $schema));
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
    return ($maxAge === Cache::PERMANENT) ? Cache::PERMANENT : (int) $this->requestStack->getMasterRequest()->server->get('REQUEST_TIME') + $maxAge;
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
