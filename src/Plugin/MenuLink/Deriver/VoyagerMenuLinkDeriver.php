<?php

namespace Drupal\graphql\Plugin\MenuLink\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\graphql\Entity\Server;

/**
 * Class VoyagerMenuLinkDeriver
 *
 * @package Drupal\graphql\Plugin\MenuLink\Deriver
 *
 * @codeCoverageIgnore
 */
class VoyagerMenuLinkDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $servers = array_keys(Server::loadMultiple());

    foreach ($servers as $id) {
      $this->derivatives[$id] = [
        'route_name' => "graphql.voyager.$id",
        'parent' => 'entity.graphql_server.collection',
      ] + $basePluginDefinition;
    }

    return $this->derivatives;
  }

}
