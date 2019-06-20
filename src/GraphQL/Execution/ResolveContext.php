<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;

// TODO: Add path based context bag.
class ResolveContext implements RefinableCacheableDependencyInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * @var \Drupal\graphql\GraphQL\ResolverRegistryInterface
   */
  protected $resolverRegistry;

  /**
   * ResolveContext constructor.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $resolverRegistry
   */
  public function __construct(ResolverRegistryInterface $resolverRegistry) {
    $this->resolverRegistry = $resolverRegistry;
  }

  /**
   * @return \Drupal\graphql\GraphQL\ResolverRegistryInterface
   */
  public function getRegistry() {
    return $this->resolverRegistry;
  }

}
