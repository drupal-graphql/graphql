<?php

namespace Drupal\graphql\GraphQL\Context;

use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;

/**
 * Non-lazy un-optimized context repository.
 *
 * Intermediate solution for graphql requests that change context within one
 * request and would confuse the LazyContextBuilder.
 */
class ContextRepository implements ContextRepositoryInterface {

  /**
   * The list of content providers.
   *
   * @var \Drupal\Core\Plugin\Context\ContextProviderInterface[]
   */
  protected $contextProviders = [];

  /**
   * Add a context provider.
   *
   * @param \Drupal\Core\Plugin\Context\ContextProviderInterface $contextProvider
   *   The context provider to add.
   */
  public function addContextProvider(ContextProviderInterface $contextProvider) {
    $this->contextProviders[] = $contextProvider;
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $contextIds) {
    $contexts = [];
    foreach ($this->contextProviders as $contextProvider) {
      foreach ($contextProvider->getRuntimeContexts($contextIds) as $id => $context) {
        $contexts[$id] = $context;
      }
    }
    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {
    $contexts = [];
    foreach ($this->contextProviders as $contextProvider) {
      foreach ($contextProvider->getAvailableContexts() as $id => $context) {
        $contexts[$id] = $context;
      }
    }
    return $contexts;
  }

}
