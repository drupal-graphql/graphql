<?php

namespace Drupal\graphql\GraphQL\Context;

use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;

/**
 * Non-lazy un-optimized context repository.
 *
 * Intermediate solution for graphql requests that change context within one
 * request and would confuse the LazyContextBuilder.
 */
class ContextRepository implements QueryContextRepositoryInterface {

  /**
   * The list of content providers.
   *
   * @var \Drupal\Core\Plugin\Context\ContextProviderInterface[]
   */
  protected $contextProviders = [];

  /**
   * @var \SplObjectStorage
   */
  protected $overrides;

  /**
   * @var \SplStack
   */
  protected $stack;

  /**
   * ContextRepository constructor.
   */
  public function __construct() {
    $this->overrides = new \SplObjectStorage();
    $this->stack = new \SplStack();
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
  public function executeInContext(ResolveContext $partition, array $path, callable $callable) {
    $key = implode('.', $path);

    // If this is a new path, inherit from the parent.
    if (!isset($this->overrides[$partition][$key])) {
      $parent = implode('.', array_slice($path, 0, -1));
      if (isset($this->overrides[$partition][$parent])) {
        $values = $this->overrides[$partition];
        $values[$key] = array_merge($values[$parent] ?? [], $values[$key] ?? []);
        $this->overrides[$partition] = $values;
      }
    }

    $this->stack->push([$partition, $key]);
    $result = $callable();
    $this->stack->pop();

    return $result;
  }

  /**
   * @inheritdoc
   */
  public function setContextValue(ResolveContext $context, array $path, $id, $value) {
    $key = implode('.', $path);
    $values = $this->overrides->offsetExists($context) ? $this->overrides[$context] : [];
    $values[$key][$id] = $value;
    $this->overrides[$context] = $values;
  }

  /**
   * @inheritdoc
   */
  public function getContextValue(ResolveContext $context, array $path, $id, $default) {
    $key = implode('.', $path);
    $values = $this->overrides->offsetExists($context) ? $this->overrides[$context] : [];
    if (!isset($values[$key])) {
      return $default;
    }

    return array_key_exists($id, $values[$key]) ? $values[$key][$id] : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $contextIds) {
    $contexts = [];

    // Read the context overrides for the top of the execution stack.
    if (!$this->stack->isEmpty()) {
      list($partition, $key) = $this->stack->top();
      if (isset($this->overrides[$partition][$key])) {
        $contexts = $this->overrides[$partition][$key];
      }
    }

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
