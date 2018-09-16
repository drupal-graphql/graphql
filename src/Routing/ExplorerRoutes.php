<?php

namespace Drupal\graphql\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\graphql\Entity\Server;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ExplorerRoutes extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $routes = new RouteCollection();
    /** @var \Drupal\graphql\Entity\ServerInterface[] $servers */
    $servers = Server::loadMultiple();

    foreach ($servers as $id => $server) {
      $path = $server->get('endpoint');

      $routes->add("graphql.explorer.$id", new Route("$path/explorer", [
        'schema' => $id,
        '_controller' => '\Drupal\graphql\Controller\ExplorerController::viewExplorer',
        '_title' => 'GraphiQL',
      ], [
        '_permission' => 'use graphql explorer',
      ], [
        '_admin_route' => 'TRUE',
      ]));
    }

    $collection->addCollection($routes);
  }

}
