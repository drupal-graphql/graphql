<?php

namespace Drupal\graphql\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\graphql\Entity\Server;
use Drupal\graphql\Plugin\SchemaPluginManager;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Registers graphql voyager routes for all schemas.
 */
class VoyagerRoutes extends RouteSubscriberBase {

  /**
   * Alters existing routes for a specific collection.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   */
  protected function alterRoutes(RouteCollection $collection) {
    $routes = new RouteCollection();
    /** @var \Drupal\graphql\Entity\ServerInterface[] $servers */
    $servers = Server::loadMultiple();

    foreach ($servers as $id => $server) {
      $path = $server->endpoint();

      $routes->add("graphql.voyager.$id", new Route("$path/voyager", [
        'schema' => $id,
        '_controller' => '\Drupal\graphql\Controller\VoyagerController::viewVoyager',
        '_title' => 'GraphQL Voyager',
      ], [
        '_permission' => 'use graphql voyager',
      ], [
        '_admin_route' => 'TRUE',
      ]));
    }

    $collection->addCollection($routes);
  }

}
