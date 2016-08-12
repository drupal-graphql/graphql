<?php

namespace Drupal\graphql;

/**
 * Generates a GraphQL Schema.
 */
class SchemaProvider implements SchemaProviderInterface {
  /**
   * {@inheritdoc}
   */
  public function getQuerySchema() {
    return array_reduce($this->getSortedProviders(), function ($carry, SchemaProviderInterface $provider) {
      return array_merge($carry, $provider->getQuerySchema());
    }, []);
  }

  /**
   * {@inheritdoc}
   */
  public function getMutationSchema() {
    return [];
  }

  /**
   * Adds a active theme negotiation service.
   *
   * @param \Drupal\graphql\SchemaProviderInterface $provider
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
   * @return \Drupal\graphql\SchemaProviderInterface[]
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
