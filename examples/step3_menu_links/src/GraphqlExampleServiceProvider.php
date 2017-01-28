<?php

namespace Drupal\graphql_example;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

class GraphqlExampleServiceProvider extends ServiceProviderBase  {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->hasParameter('graphql.config')) {
      $config = $container->getParameter('graphql.config');
      // Replace the automatically generated schema with a custom one.
      $config['schema_class'] = 'Drupal\graphql_example\GraphQL\Relay\Schema';
      // Disable schema caching for demo purposes.
      $config['cache'] = FALSE;

      $container->setParameter('graphql.config', $config);
    }

    if ($container->has('graphql.schema_provider')) {
      $definition = $container->getDefinition('graphql.schema_provider');
      if ($definition->getClass() === 'Drupal\graphql\SchemaProvider\SchemaProvider') {
        $definition->setClass('Drupal\graphql_example\SchemaProvider\SchemaProvider');
        $definition->clearTag('service_collector');
      }
    }
  }
}
