<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\graphql\GraphQL\Context\QueryContextInterface;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use GraphQL\Type\Definition\ResolveInfo;

class ResolveContext implements RefinableCacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * @var \Drupal\graphql\GraphQL\Context\QueryContextInterface
   */
  protected $contextRepository;

  /**
   * @var \Drupal\graphql\GraphQL\ResolverRegistryInterface
   */
  protected $resolverRegistry;

  /**
   * ResolveContext constructor.
   *
   * @param \Drupal\graphql\GraphQL\Context\QueryContextInterface $contextRepository
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $resolverRegistry
   */
  public function __construct(QueryContextInterface $contextRepository, ResolverRegistryInterface $resolverRegistry) {
    $this->contextRepository = $contextRepository;
    $this->resolverRegistry = $resolverRegistry;
  }

  /**
   * @return \Drupal\graphql\GraphQL\ResolverRegistryInterface
   */
  public function getRegistry() {
    return $this->resolverRegistry;
  }

  public function executeInContext(callable $callable, ResolveInfo $info) {
    $this->contextRepository->executeInContext($this, $info->path, $callable);
  }

  /**
   * Sets a contextual value for the current field and its descendants.
   *
   * Allows field resolvers to set contextual values which can be inherited by
   * their descendants.
   *
   * @param string $name
   *   The name of the context.
   * @param $value
   *   The value of the context.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve info object.
   *
   * @return $this
   */
  public function setContext($name, $value, ResolveInfo $info) {
    $this->contextRepository->overrideContext($this, $info->path, $name, $value);
    return $this;
  }

}
