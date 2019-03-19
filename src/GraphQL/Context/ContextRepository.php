<?php

namespace Drupal\graphql\GraphQL\Context;

use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;

/**
 * Non-lazy un-optimized context repository.
 *
 * Intermediate solution for graphql requests that change context within one
 * request and would confuse the LazyContextBuilder.
 */
class ContextRepository implements ContextRepositoryInterface, QueryContextInterface {

  /**
   * The list of content providers.
   *
   * @var \Drupal\Core\Plugin\Context\ContextProviderInterface[]
   */
  protected $contextProviders = [];

  protected $overrides;

  protected $currentPath;

  protected $currentContext;

  public function __construct() {
    $this->overrides = new \SplObjectStorage();
  }


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
   * @inheritdoc
   */
  public function executeInContext(ResolveContext $context, array $path, callable $callable) {
    if ($path !== $this->currentPath || $context !== $this->currentContext) {
      $this->currentPath = $path;
      $this->currentContext = $context;

      $key = implode('.', $path);
      $parentKey = implode('.', array_slice($path, 0, -1));
      if (isset($this->overrides[$context][$parentKey])) {
        $values = $this->overrides[$context];
        $values[$key] = array_merge($values[$parentKey] ?? [], $values[$key] ?? []);
        $this->overrides[$context] = $values;
      }
    }
    $result = $callable();
    return $result;
  }

  /**
   * @inheritdoc
   */
  public function overrideContext(ResolveContext $context, array $path, $id, $value) {
    $key = implode('.', $path);
    $values = $this->overrides->offsetExists($context) ? $this->overrides[$context] : [];
    $values[$key][$id] = $value;
    $this->overrides[$context] = $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $contextIds) {
    $key = implode('.', $this->currentPath);
    $contexts = $this->overrides[$this->currentContext][$key] ?? [];

    foreach ($this->contextProviders as $contextProvider) {
      foreach ($contextProvider->getRuntimeContexts($contextIds) as $id => $context) {
        if (!isset($contexts[$id])) {
          $contexts[$id] = $context;
        }
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
