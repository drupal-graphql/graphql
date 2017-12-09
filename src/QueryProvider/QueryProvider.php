<?php

namespace Drupal\graphql\QueryProvider;

class QueryProvider implements QueryProviderInterface {

  /**
   * Unsorted list of query providers nested and keyed by priority.
   *
   * @var \Drupal\graphql\QueryProvider\QueryProviderInterface[]
   */
  protected $providers = [];

  /**
   * Sorted list of query providers.
   *
   * @var \Drupal\graphql\QueryProvider\QueryProviderInterface[]
   */
  protected $sortedProviders;

  /**
   * {@inheritdoc}
   */
  public function getQuery(array $params) {
    foreach ($this->getSortedProviders() as $provider) {
      if ($query = $provider->getQuery($params)) {
        return $query;
      }
    }

    return NULL;
  }

  /**
   * Adds a query provider.
   *
   * @param \Drupal\graphql\QueryProvider\QueryProviderInterface $provider
   *   The query provider to add.
   * @param int $priority
   *   Priority of the query provider.
   */
  public function addQueryProvider(QueryProviderInterface $provider, $priority = 0) {
    $this->providers[$priority][] = $provider;
    $this->sortedProviders = NULL;
  }

  /**
   * Returns the sorted array of query providers.
   *
   * @return \Drupal\graphql\QueryProvider\QueryProviderInterface[]
   *   An array of query provider objects.
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
