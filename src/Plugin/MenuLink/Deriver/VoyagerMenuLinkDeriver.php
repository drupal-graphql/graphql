<?php

namespace Drupal\graphql\Plugin\MenuLink\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\graphql\Entity\Server;

class VoyagerMenuLinkDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $servers = array_keys(Server::loadMultiple());

    foreach ($servers as $id) {
      $this->derivatives[$id] = [
        'route_name' => "graphql.voyager.$id",
      ] + $basePluginDefinition;
    }

    return $this->derivatives;
  }

}
