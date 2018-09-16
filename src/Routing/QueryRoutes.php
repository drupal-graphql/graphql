<?php

namespace Drupal\graphql\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\graphql\Entity\Server;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class QueryRoutes extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $routes = new RouteCollection();
    /** @var \Drupal\graphql\Entity\ServerInterface[] $servers */
    $servers = Server::loadMultiple();

    foreach ($servers as $id => $server) {
      $path = $server->get('endpoint');

      $routes->add("graphql.query.$id", new Route($path, [
        'schema' => $id,
        '_graphql' => TRUE,
        '_controller' => '\Drupal\graphql\Controller\RequestController::handleRequest',
      ], [
        '_graphql_query_access' => 'TRUE',
      ]));
    }

    $collection->addCollection($routes);
  }

}
