<?php

namespace Drupal\graphql\QueryMapProvider;

class QueryMapProvider implements QueryMapProviderInterface {
  /**
   * Unsorted list of query map providers nested and keyed by priority.
   *
   * @var \Drupal\graphql\QueryMapProvider\QueryMapProviderInterface[]
   */
  protected $providers = [];

  /**
   * Sorted list of query map providers.
   *
   * @var \Drupal\graphql\QueryMapProvider\QueryMapProviderInterface[]
   */
  protected $sortedProviders;

  /**
   * {@inheritdoc}
   */
  public function getQuery($version, $id) {
    foreach ($this->getSortedProviders() as $provider) {
      if ($query = $provider->getQuery($version, $id)) {
        return $query;
      }
    }

    return NULL;
  }

  /**
   * Adds a query map provider.
   *
   * @param \Drupal\graphql\QueryMapProvider\QueryMapProviderInterface $provider
   *   The query map provider to add.
   * @param int $priority
   *   Priority of the query map provider.
   */
  public function addQueryMapProvider(QueryMapProviderInterface $provider, $priority = 0) {
    $this->providers[$priority][] = $provider;
    $this->sortedProviders = NULL;
  }

  /**
   * Returns the sorted array of query map providers.
   *
   * @return \Drupal\graphql\QueryMapProvider\QueryMapProviderInterface[]
   *   An array of query map provider objects.
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
