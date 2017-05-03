<?php

namespace Drupal\graphql;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\graphql\GraphQL\Validator\ConfigValidator\Rules\TypeValidationRule;
use Drupal\graphql\SchemaProvider\SchemaProviderInterface;
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
   * Constructs a SchemaFactory object.
   *
   * @param \Drupal\Core\Cache\Context\CacheContextsManager $contextsManager
   *   The cache contexts manager service.
   * @param \Drupal\graphql\SchemaProvider\SchemaProviderInterface $schemaProvider
   *   The schema provider service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $schemaCache
   *   The schema cache backend.
   * @param array $config
   *   The configuration provided through the services.yml.
   */
  public function __construct(CacheContextsManager $contextsManager, SchemaProviderInterface $schemaProvider, CacheBackendInterface $schemaCache, array $config) {
    $this->config = $config;

    // Override the default type validator to enable services as field resolver
    // callbacks.
    $validator = ConfigValidator::getInstance();
    $validator->addRule('type', new TypeValidationRule($validator));

    $this->schemaProvider = $schemaProvider;
    $this->contextsManager = $contextsManager;
    $this->schemaCache = $schemaCache;
  }

  /**
   * Loads and caches the generated schema.
   *
   * @return \Youshido\GraphQL\Schema\AbstractSchema
   *   The generated GraphQL schema.
   */
  public function getSchema() {
    $contexts = $this->schemaProvider->getContexts();
    $parts = $this->contextsManager->convertTokensToKeys($contexts)->getKeys();
    $cid = implode(':', array_merge(['schema'], $parts));

    if ($this->config['cache'] && $schema = $this->schemaCache->get($cid)) {
      return $schema->data;
    }

    $schema = $this->schemaProvider->getSchema();

    if ($this->config['cache']) {
      // Cache the generated schema in the configured cache backend.
      $metadata = new CacheableMetadata();
      $metadata->setCacheMaxAge(Cache::PERMANENT);

      if ($schema instanceof CacheableDependencyInterface) {
        $metadata->addCacheableDependency($schema);
      }

      $this->schemaCache->set($cid, $schema, $metadata->getCacheMaxAge(), $metadata->getCacheTags());
    }

    return $schema;
  }
}
