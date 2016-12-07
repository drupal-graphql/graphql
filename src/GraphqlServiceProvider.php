<?php

namespace Drupal\graphql;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

class GraphqlServiceProvider extends ServiceProviderBase  {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->has('page_cache_request_policy')) {
      $definition = $container->getDefinition('page_cache_request_policy');
      if ($definition->getClass() === 'Drupal\graphql\Cache\DefaultRequestPolicyOverride') {
        $definition->setClass('Drupal\graphql\Cache\DefaultRequestPolicyOverride');
      }
    }
  }
}
