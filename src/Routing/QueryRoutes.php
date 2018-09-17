<?php

namespace Drupal\graphql\Routing;

use Drupal\graphql\Entity\Server;
use Symfony\Component\Routing\Route;

class QueryRoutes {

  /**
   * Collects routes for the server endpoints.
   */
  public function routes() {
    /** @var \Drupal\graphql\Entity\ServerInterface[] $servers */
    $servers = Server::loadMultiple();
    $routes = [];

    foreach ($servers as $id => $server) {
      $path = $server->get('endpoint');

      $routes["graphql.query.$id"] = (new Route($path))
        ->addDefaults([
          'schema' => $id,
          '_graphql' => TRUE,
          '_controller' => '\Drupal\graphql\Controller\RequestController::handleRequest',
        ])
        ->addRequirements([
          '_graphql_query_access' => 'TRUE',
        ])
        ->addOptions([
          'no_cache' => TRUE,
        ]);
    }

    return $routes;
  }

}
