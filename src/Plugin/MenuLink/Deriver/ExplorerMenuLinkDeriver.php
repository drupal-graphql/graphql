<?php

namespace Drupal\graphql\Plugin\MenuLink\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\graphql\Entity\Server;

/**
 * Class ExplorerMenuLinkDeriver
 *
 * @package Drupal\graphql\Plugin\MenuLink\Deriver
 *
 * @codeCoverageIgnore
 */
class ExplorerMenuLinkDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $servers = array_keys(Server::loadMultiple());

    foreach ($servers as $id) {
      $this->derivatives[$id] = [
        'route_name' => "graphql.explorer.$id",
        'parent' => 'entity.graphql_server.collection',
      ] + $basePluginDefinition;
    }

    return $this->derivatives;
  }

}
