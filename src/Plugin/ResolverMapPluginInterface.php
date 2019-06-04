<?php

namespace Drupal\graphql\Plugin;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;

/**
 * Defines the common interface for all resolver map plugins.
 *
 * @see \Drupal\graphql\Plugin\ResolverMapPluginManager
 * @see \Drupal\graphql\Annotation\ResolverMap
 * @see plugin_api
 */
interface ResolverMapPluginInterface {

  /**
   * Register field/type resolvers.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   *   Resolver registry.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   Resolver builder.
   */
  public function registerResolvers(ResolverRegistry $registry, ResolverBuilder $builder);

}
