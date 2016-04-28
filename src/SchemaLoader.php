<?php

/**
 * @file
 * Contains \Drupal\graphql\SchemaLoader.
 */

namespace Drupal\graphql;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\LanguageInterface;
use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;

/**
 * Loads and caches a generated GraphQL schema.
 */
class SchemaLoader {
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
   * Constructs a SchemaLoader object.
   *
   * @param \Drupal\graphql\SchemaProviderInterface $schema_provider
   *   The schema provider service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $schema_cache
   *   The schema cache backend.
   */
  public function __construct(SchemaProviderInterface $schema_provider, CacheBackendInterface $schema_cache) {
    $this->schemaProvider = $schema_provider;
    $this->schemaCache = $schema_cache;
  }

  /**
   * Loads and caches the generated schema.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language for which to fetch the schema.
   *
   * @return \Fubhy\GraphQL\Schema The generated GraphQL schema.
   *   The generated GraphQL schema.
   */
  public function loadSchema(LanguageInterface $language) {
    if ($schema = $this->schemaCache->get($language->getId())) {
      //return $schema->data;
    }

    $query = new ObjectType('Root', $this->schemaProvider->getQuerySchema());
    $mutation = $this->schemaProvider->getMutationSchema();
    $mutation = $mutation ? new ObjectType('Mutation', $mutation) : NULL;
    $schema = new Schema($query, $mutation);

    // Resolve the type map to get rid of any closures before serialization.
    $schema->getTypeMap();

    // Cache the generated schema in the configured cache backend.
    $expire = Cache::PERMANENT;
    $tags  = ['views', 'entity_field_info', 'entity_bundles'];
    $cid = $language->getId();
    $this->schemaCache->set($cid, $schema, $expire, $tags);

    return $schema;
  }
}
