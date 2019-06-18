<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\graphql\GraphQL\Context\QueryContextRepositoryInterface;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use GraphQL\Type\Definition\ResolveInfo;

class ResolveContext implements RefinableCacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * @var \Drupal\graphql\GraphQL\Context\QueryContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * @var \Drupal\graphql\GraphQL\ResolverRegistryInterface
   */
  protected $resolverRegistry;

  /**
   * ResolveContext constructor.
   *
   * @param \Drupal\graphql\GraphQL\Context\QueryContextRepositoryInterface $contextRepository
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $resolverRegistry
   */
  public function __construct(QueryContextRepositoryInterface $contextRepository, ResolverRegistryInterface $resolverRegistry) {
    $this->contextRepository = $contextRepository;
    $this->resolverRegistry = $resolverRegistry;
  }

  /**
   * @return \Drupal\graphql\GraphQL\ResolverRegistryInterface
   */
  public function getRegistry() {
    return $this->resolverRegistry;
  }

  /**
   * @return \Drupal\graphql\GraphQL\Context\QueryContextRepositoryInterface
   */
  public function getContextRepository() {
    return $this->contextRepository;
  }

  /**
   * @param callable $callable
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return mixed
   */
  public function executeInContext(callable $callable, ResolveInfo $info) {
    return $this->contextRepository->executeInContext($this, $info->path, $callable);
  }

}
