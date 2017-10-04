<?php

namespace Drupal\graphql_twig;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

class GraphQLTwigServiceProvider extends ServiceProviderBase {

  public function alter(ContainerBuilder $container) {
    $container->getDefinition('twig')
      ->setClass(GraphQLTwigEnvironment::class);
  }

}