<?php

namespace Drupal\graphql_example\GraphQL\Field;

use Drupal\Core\Entity\EntityStorageInterface;
use Youshido\GraphQL\Relay\Connection\ArrayConnection;

trait EntityArrayConnectionFieldTrait {

  /**
   * Helper function to resolve connections with entity ids.
   *
   * @param array $ids
   *   Complete list of entity ids for this connection.
   * @param array $args
   *   Array of resolver arguments.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage service to use.
   * @return array|null
   *   The resolved connection.
   */
  protected function resolveEntityArrayConnection(array $ids, array $args, EntityStorageInterface $storage) {
    if (empty($ids)) {
      return NULL;
    }

    $connection = ArrayConnection::connectionFromArray(array_combine($ids, $ids), $args);
    $ids = array_map(function ($edge) {
      return $edge['node'];
    }, $connection['edges']);

    // Load entities after processing the connection so we don't needlessly
    // load any entity objects that are then purged from the array.
    $entities = $storage->loadMultiple($ids);

    // Overwrite the edges with the loaded entities.
    $connection['edges'] = array_reduce($connection['edges'], function ($carry, $edge) use ($entities) {
      if (empty($entities[$edge['node']])) {
        return $carry;
      }

      $edge['node'] = $entities[$edge['node']];
      if ($edge['node']->access('view')) {
        array_push($carry, $edge);
      }

      return $carry;
    }, []);

    return $connection;
  }
}