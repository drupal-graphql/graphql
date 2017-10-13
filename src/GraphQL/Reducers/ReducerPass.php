<?php

namespace Drupal\graphql\GraphQL\Reducers;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds a graphql.reducers parameter to the container.
 */
class ReducerPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    $reducers = [];
    foreach ($container->findTaggedServiceIds('graphql_reducer') as $id => $tags) {
      foreach ($tags as $tag) {
        if (strpos($id, 'graphql.reducer.') !== 0) {
          throw new \InvalidArgumentException(sprintf('The service "%s" has an invalid id: Reducers must use the "graphql.reducer." prefix.', $id));
        }

        $priority = !empty($tag['priority']) ? $tag['priority'] : 0;
        $reducers[$priority][] = substr($id, 16);
      }
    }

    krsort($reducers);

    $container->setParameter('graphql.reducers', array_reduce($reducers, function(array $carry, $current) {
      return array_merge($carry, $current);
    }, []));
  }

}
