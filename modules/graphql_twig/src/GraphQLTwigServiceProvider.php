<?php

namespace Drupal\graphql_twig;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

/**
 * Service provider to inject a custom derivation of `TwigEnvironment`.
 */
class GraphQLTwigServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Replace the twig environment with the GraphQL enhanced one.
    $container->getDefinition('twig')
      ->setClass(GraphQLTwigEnvironment::class);
  }

}