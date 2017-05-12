<?php

namespace Drupal\graphql;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\graphql\GraphQL\TypeCollector;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\graphql\GraphQL\Validator\ConfigValidator\Rules\TypeValidationRule;
use Drupal\graphql\SchemaProvider\SchemaProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Type\InputObject\AbstractInputObjectType;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Validator\ConfigValidator\ConfigValidator;

/**
 * Loads and caches a generated GraphQL schema.
 */
class SchemaFactory {
  /**
   * The cache contexts manager service.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager
   */
  protected $contextsManager;

  /**
   * The schema provider service.
   *
   * @var \Drupal\graphql\SchemaProvider\SchemaProviderInterface
   */
  protected $schemaProvider;

  /**
   * The schema cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $schemaCache;

  /**
   * The service configuration.
   *
   * @var array
   */
  protected $config;

  /**
   * Extra cache metadata to add to every schema.
   *
   * @var \Drupal\Core\Cache\CacheableMetadata
   */
  protected $metadata;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a SchemaFactory object.
   *
   * @param \Drupal\Core\Cache\Context\CacheContextsManager $contextsManager
   *   The cache contexts manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   * @param \Drupal\graphql\SchemaProvider\SchemaProviderInterface $schemaProvider
   *   The schema provider service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $schemaCache
   *   The schema cache backend.
   * @param array $config
   *   The configuration provided through the services.yml.
   */
  public function __construct(
    CacheContextsManager $contextsManager,
    RequestStack $requestStack,
    SchemaProviderInterface $schemaProvider,
    CacheBackendInterface $schemaCache,
    array $config
  ) {
    $this->config = $config;

    // Override the default type validator to enable services as field resolver
    // callbacks.
    $validator = ConfigValidator::getInstance();
    $validator->addRule('type', new TypeValidationRule($validator));

    $this->schemaProvider = $schemaProvider;
    $this->contextsManager = $contextsManager;
    $this->schemaCache = $schemaCache;
    $this->metadata = new CacheableMetadata();
    $this->requestStack = $requestStack;
  }

  /**
   * Loads and caches the generated schema.
   *
   * @return \Youshido\GraphQL\Schema\AbstractSchema
   *   The generated GraphQL schema.
   */
  public function getSchema() {
    // The cache key is made up of all of the globally known cache contexts.
    $cid = $this->getCacheIdentifier($this->metadata);
    if ($this->config['cache'] && ($schema = $this->schemaCache->get($cid)) && $schema->data instanceof AbstractSchema) {
      return $schema->data;
    }

    // If the schema is not cacheable, just return it directly.
    $schema = $this->schemaProvider->getSchema();
    if (!($schema instanceof CacheableDependencyInterface)) {
      return $schema;
    }

    // Add global and field/type cache metadata to the schema.
    if ($schema instanceof RefinableCacheableDependencyInterface) {
      $schema->addCacheableDependency($this->metadata);
      $schema->addCacheableDependency($this->getCacheMetadataFromTypes($schema));
    }

    if ($this->config['cache']) {
      $tags = $schema->getCacheTags();
      $expire = $this->maxAgeToExpire($schema->getCacheMaxAge());

      // We use the cache key from the global cache metadata but the tags and
      // expiry time from the entire cache metadata.
      $this->schemaCache->set($cid, $schema, $expire, $tags);
    }

    return $schema;
  }

  /**
   * Collects cache metadata from all types registered with a schema.
   *
   * @param \Youshido\GraphQL\Schema\AbstractSchema $schema
   *   The schema to collect the metadata for.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cache metadata collected from the schema's types.
   */
  protected function getCacheMetadataFromTypes(AbstractSchema $schema) {
    $metadata = new CacheableMetadata();
    $metadata->setCacheMaxAge(Cache::PERMANENT);

    foreach (TypeCollector::collectTypes($schema) as $type) {
      if ($type instanceof CacheableDependencyInterface) {
        $metadata->addCacheableDependency($type);

        if ($type instanceof AbstractObjectType || $type instanceof AbstractInputObjectType || $type instanceof AbstractInterfaceType) {
          foreach ($type->getFields() as $field) {
            $metadata->addCacheableDependency($field);
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
   * @param \Drupal\Core\Cache\CacheableDependencyInterface $metadata
   *   Optional array of cache context tokens.
   *
   * @return string The generated cache identifier.
   *   The generated cache identifier.
   */
  protected function getCacheIdentifier(CacheableDependencyInterface $metadata) {
    $tokens = $metadata->getCacheContexts();
    $keys = $this->contextsManager->convertTokensToKeys($tokens)->getKeys();
    return implode(':', array_merge(['graphql'], array_values($keys)));
  }

  /**
   * Adds extra (global) cache metadata for every query.
   *
   * @param \Drupal\Core\Cache\CacheableDependencyInterface $metadata
   *   Extra cache metadata to merge with the cache metadata of each query.
   */
  public function addExtraCacheMetadata(CacheableDependencyInterface $metadata) {
    $this->metadata->addCacheableDependency($metadata);
  }
}
