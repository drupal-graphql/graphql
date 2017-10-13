<?php

namespace Drupal\graphql\GraphQL\Schema;

use Drupal\graphql\GraphQL\Utility\TypeCollector;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\graphql\GraphQL\Validator\TypeValidationRule;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Type\InputObject\AbstractInputObjectType;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Validator\ConfigValidator\ConfigValidator;

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
   * Constructs a SchemaFactory object.
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

    // Override the default type validator to enable services as field resolver
    // callbacks.
    $validator = ConfigValidator::getInstance();
    $validator->addRule('type', new TypeValidationRule($validator));

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
    // The cache key is made up of all of the globally known cache contexts.
    if (!empty($this->config['schema_cache'])) {
      if (($contextCache = $this->metadataCache->get($name)) && $contextCache->data) {
        $cid = $this->getCacheIdentifier($name, $contextCache->data);

        if (($schema = $this->schemaCache->get($cid)) && $schema->data instanceof SchemaPluginInterface) {
          return $schema->data;
        }
      }
    }

    $schema = $this->schemaManager->createInstance($name);
    // If the schema is not cacheable, just return it directly.
    if (!$schema instanceof SchemaPluginInterface || empty($this->config['schema_cache'])) {
      return $schema;
    }

    // Compute the cache identifier, tag and expiry time.
    $metadata = $schema->getSchemaCacheMetadata();
    if ($metadata->getCacheMaxAge() === 0) {
      return $schema;
    }

    $tags = $metadata->getCacheTags();
    $expire = $this->maxAgeToExpire($metadata->getCacheMaxAge());
    $cid = $this->getCacheIdentifier($name, $metadata);

    // Write the cache entry for the cache metadata.
    $this->metadataCache->set($name, $metadata, $expire, $tags);

    // We use the cache key from the global cache metadata but the tags and
    // expiry time from the entire cache metadata.
    $this->schemaCache->set($cid, $schema, $expire, $tags);

    return $schema;
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
