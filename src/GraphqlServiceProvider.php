<?php

namespace Drupal\graphql;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\graphql\GraphQL\Reducers\ReducerPass;

class GraphqlServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    $container->addCompilerPass(new ReducerPass());
  }

}
