<?php

namespace Drupal\graphql;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\graphql\GraphQL\Relay\Schema;
use Drupal\graphql\GraphQL\Validator\ConfigValidator\Rules\TypeValidationRule;
use Youshido\GraphQL\Validator\ConfigValidator\ConfigValidator;

/**
 * Loads and caches a generated GraphQL schema.
 */
class SchemaFactory {
  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The schema provider service.
   *
   * @var \Drupal\graphql\SchemaProviderInterface
   */
  protected $schemaProvider;

  /**
   * The schema cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $schemaCache;

  /**
   * Constructs a SchemaFactory object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   * @param \Drupal\graphql\SchemaProviderInterface $schema_provider
   *   The schema provider service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $schema_cache
   *   The schema cache backend.
   */
  public function __construct(LanguageManagerInterface $languageManager, SchemaProviderInterface $schema_provider, CacheBackendInterface $schema_cache) {
    // Override the default type validator to enable services as field resolver
    // callbacks.
    $validator = ConfigValidator::getInstance();
    $validator->addRule('type', new TypeValidationRule($validator));

    $this->schemaProvider = $schema_provider;
    $this->languageManager = $languageManager;
    $this->schemaCache = $schema_cache;
  }

  /**
   * Loads and caches the generated schema.
   *
   * @return \Drupal\graphql\GraphQL\Relay\Schema The generated GraphQL schema.
   *   The generated GraphQL schema.
   */
  public function getSchema() {
    $language = $this->languageManager->getCurrentLanguage();
    if ($schema = $this->schemaCache->get($language->getId())) {
      return $schema->data;
    }

    $query = $this->schemaProvider->getQuerySchema();
    $mutation = $this->schemaProvider->getMutationSchema();
    $schema = new Schema($query, $mutation);

    // Cache the generated schema in the configured cache backend.
    $tags  = ['views', 'entity_field_info', 'entity_bundles'];
    $this->schemaCache->set($language->getId(), $schema, Cache::PERMANENT, $tags);

    return $schema;
  }
}
