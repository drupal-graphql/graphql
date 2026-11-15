<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\MenuLink\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\graphql\Entity\Server;

/**
 * Generates one menu link per GraphQL server for Explorer.
 *
 * @package Drupal\graphql\Plugin\MenuLink\Deriver
 *
 * @codeCoverageIgnore
 */
class ExplorerMenuLinkDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    assert(is_array($base_plugin_definition));
    $servers = array_keys(Server::loadMultiple());

    foreach ($servers as $id) {
      $this->derivatives[$id] = [
        'route_name' => "graphql.explorer.$id",
        'parent' => 'entity.graphql_server.collection',
      ] + $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
