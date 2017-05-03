<?php

namespace Drupal\graphql\SchemaProvider;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\Schema;

/**
 * Generates a GraphQL Schema.
 */
class SchemaProvider implements SchemaProviderInterface {
  /**
   * Unsorted list of schema providers nested and keyed by priority.
   *
   * @var \Drupal\graphql\SchemaProvider\SchemaProviderInterface[]
   */
  protected $providers = [];

  /**
   * Sorted list of schema providers.
   *
   * @var \Drupal\graphql\SchemaProvider\SchemaProviderInterface[]
   */
  protected $sortedProviders;

  /**
   * The configuration provided through the services.yml.
   *
   * @var array
   */
  protected $config;

  /**
   * Constructs a SchemaProvider object.
   *
   * @param array $config
   *   The configuration provided through the services.yml.
   */
  public function __construct(array $config) {
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    $schemaClass = $this->config['schema_class'];

    return array_reduce($this->getSortedProviders(), function (Schema $carry, SchemaProviderInterface $provider) {
      if ($schema = $provider->getSchema()) {
        $carry->getTypesList()->addTypes($schema->getTypesList()->getTypes());
        $carry->getQueryType()->addFields($schema->getQueryType()->getFields());

        if ($schema->getMutationType()->hasFields()) {
          $carry->getMutationType()->addFields($schema->getMutationType()->getFields());
        }
      }

      return $carry;
    }, new $schemaClass());
  }

  /**
   * {@inheritdoc}
   */
  public function getContexts() {
    $metadata = array_reduce($this->getSortedProviders(), function (CacheableMetadata $carry, SchemaProviderInterface $provider) {
      if ($contexts = $provider->getContexts()) {
        $carry->addCacheContexts($contexts);
      }

      return $carry;
    }, new CacheableMetadata());

    return $metadata->getCacheContexts();
  }

  /**
   * Adds a schema provider.
   *
   * @param \Drupal\graphql\SchemaProvider\SchemaProviderInterface $provider
   *   The schema provider to add.
   * @param int $priority
   *   Priority of the schema provider.
   */
  public function addSchemaProvider(SchemaProviderInterface $provider, $priority = 0) {
    $this->providers[$priority][] = $provider;
    $this->sortedProviders = NULL;
  }

  /**
   * Returns the sorted array of schema providers.
   *
   * @return \Drupal\graphql\SchemaProvider\SchemaProviderInterface[]
   *   An array of schema provider objects.
   */
  protected function getSortedProviders() {
    if (!isset($this->sortedProviders)) {
      krsort($this->providers);

      $this->sortedProviders = [];
      foreach ($this->providers as $providers) {
        $this->sortedProviders = array_merge($this->sortedProviders, $providers);
      }
    }

    return $this->sortedProviders;
  }
}
