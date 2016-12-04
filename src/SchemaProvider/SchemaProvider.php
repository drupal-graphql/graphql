<?php

namespace Drupal\graphql\SchemaProvider;

/**
 * Generates a GraphQL Schema.
 */
class SchemaProvider implements SchemaProviderInterface {
  /**
   * Unsorted list of schema providers nested and keyed by priority.
   *
   * @var \Drupal\graphql\SchemaProvider\SchemaProviderInterface[]
   */
  protected $providers;

  /**
   * Sorted list of schema providers.
   *
   * @var \Drupal\graphql\SchemaProvider\SchemaProviderInterface[]
   */
  protected $sortedProviders;

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return array_unique(call_user_func_array('array_merge', array_map(function (SchemaProviderInterface $provider) {
      return $provider->getCacheTags();
    }, $this->getSortedProviders())));
  }

  /**
   * {@inheritdoc}
   */
  public function getQuerySchema() {
    return array_reduce($this->getSortedProviders(), function ($carry, SchemaProviderInterface $provider) {
      return array_merge($carry, $provider->getQuerySchema() ?: []);
    }, []);
  }

  /**
   * {@inheritdoc}
   */
  public function getMutationSchema() {
    return array_reduce($this->getSortedProviders(), function ($carry, SchemaProviderInterface $provider) {
      return array_merge($carry, $provider->getMutationSchema() ?: []);
    }, []);
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
