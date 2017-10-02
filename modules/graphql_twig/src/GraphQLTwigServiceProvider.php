<?php

namespace Drupal\graphql_twig;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Symfony\Component\DependencyInjection\Reference;

class GraphQLTwigServiceProvider implements ServiceModifierInterface {

  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('twig');
    $definition->setClass('Drupal\graphql_twig\GraphQLTwigEnvironment');
    $definition->addArgument(new Reference('graphql.query_processor'));
  }

}