<?php

namespace Drupal\graphql\Plugin\MenuLink\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\graphql\Entity\Server;

class ExplorerMenuLinkDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $servers = array_keys(Server::loadMultiple());

    foreach ($servers as $id) {
      $this->derivatives[$id] = [
        'route_name' => "graphql.explorer.$id",
      ] + $basePluginDefinition;
    }

    return $this->derivatives;
  }

}
